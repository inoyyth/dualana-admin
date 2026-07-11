<?php
/**
 * GWL Register REST API Routes
 *
 * @package REST API ENDPOINTS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GWL_Register_Route_API {

	/**
	 * GWL_Register_Routes constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'gwl_rest_api_endpoints' ) );
	}

	/**
	 * Build meta_query query args when meta_keys are provided in the request.
	 *
	 * Omits meta_query when no filters are set to avoid unnecessary slow queries.
	 *
	 * @param mixed $meta_keys Meta key/value pairs from the request.
	 * @return array Query args containing meta_query, or empty array.
	 */
	private function gwl_get_meta_query_args( $meta_keys ) {
		if ( empty( $meta_keys ) || ! is_array( $meta_keys ) ) {
			return array();
		}

		$meta_query = array(
			'relation' => 'AND',
		);

		foreach ( $meta_keys as $key => $value ) {
			$meta_query[] = array(
				'key'     => $key,
				'value'   => $value,
				'compare' => 'LIKE',
			);
		}

		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Only applied when API consumers pass meta_keys.
		return array( 'meta_query' => $meta_query );
	}

	/**
	 * Register user endpoints.
	 */
	function gwl_rest_api_endpoints() {

		// Get plugin settings.
		$awpr_user_login_api = get_option( 'awpr_user_login_api' );
		$awpr_post_api       = get_option( 'awpr_post_api' );
		$awpr_user_api       = get_option( 'awpr_user_api' );
		$awpr_product_api    = get_option( 'awpr_product_api' );

		$route_args = array(
			'permission_callback' => '__return_true',
		);
		$public_methods = WP_REST_Server::READABLE . ', ' . WP_REST_Server::CREATABLE;

		if ( 'yes' === $awpr_user_login_api ) {
			/**
			 * Handle User Login request.
			 *
			 * This endpoint takes 'username' and 'password' in the body of the request.
			 * Returns the user object on success
			 * Also handles error by returning the relevant error if the fields are empty or credentials don't match.
			 *
			 * Example: http://example.com/wp-json/api/v2/user/login
			 */
			register_rest_route(
				'api/v2',
				'/user/login',
				array_merge(
					$route_args,
					array(
						'methods'  => $public_methods,
						'callback' => array( $this, 'gwl_rest_user_login_endpoint_handler' ),
					)
				)
			);
		}

		if ( 'yes' === $awpr_post_api ) {
			/**
			 * Handle Post request.
			 *
			 * This endpoint takes 'post_id', 'post_type', and 'meta_keys' in the body of the request.
			 * Returns the post object on success
			 *
			 * Example: http://example.com/wp-json/api/v2/postsData
			 */
			register_rest_route(
				'api/v2',
				'/postsData',
				array_merge(
					$route_args,
					array(
						'methods'  => $public_methods,
						'callback' => array( $this, 'gwl_rest_posts_metadata_endpoint_handler' ),
					)
				)
			);
		}

		if ( 'yes' === $awpr_user_api ) {
			/**
			 * Handle User request.
			 *
			 * This endpoint takes 'user_id', 'role' and 'meta_keys' in the body of the request.
			 * Returns the user object on success
			 *
			 * Example: http://example.com/wp-json/api/v2/usersData
			 */
			register_rest_route(
				'api/v2',
				'/usersData',
				array_merge(
					$route_args,
					array(
						'methods'  => $public_methods,
						'callback' => array( $this, 'gwl_rest_users_metadata_endpoint_handler' ),
					)
				)
			);
		}

		if ( 'yes' === $awpr_product_api ) {
			/**
			 * Handle Product request.
			 *
			 * This endpoint takes 'product_id' and 'meta_keys' in the body of the request.
			 * Returns the product object on success
			 *
			 * Example: http://example.com/wp-json/api/v2/productsData
			 */
			register_rest_route(
				'api/v2',
				'/productsData',
				array_merge(
					$route_args,
					array(
						'methods'  => $public_methods,
						'callback' => array( $this, 'gwl_rest_products_endpoint_handler' ),
					)
				)
			);
		}
	}

	/**
	 * User Login call back.
	 *
	 * @param WP_REST_Request $request Login request parameter.
	 */
	function gwl_rest_user_login_endpoint_handler( WP_REST_Request $request ) {
		$response = array();
		$parameters = $request->get_params();

		$username =  isset($parameters['username']) ? sanitize_text_field($parameters['username']) : '';
		$password = isset($parameters['password']) ? sanitize_text_field($parameters['password']) : '';

		// Error Handling.
		$error = new WP_Error();

		if ( empty( $username ) && ! empty( $password ) ) {
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'Username field is required';
			return new WP_REST_Response( $response );
		} elseif ( ! empty( $username ) && empty( $password ) ) {
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'Password field is required';
			return new WP_REST_Response( $response );
		} elseif ( empty( $username ) && empty( $password ) ) {
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'Username & Password field is required';
			return new WP_REST_Response( $response );
		}

		$user = wp_authenticate( $username, $password );

		// If user found.
		if ( ! is_wp_error( $user ) ) {
			$response['status'] = 200;
			$user_id = $user->data->ID;
			$users = get_user_by( 'id', $user_id );
			$upload_id = get_user_meta( $user_id, 'picture', true );
			$image_attributes = wp_get_attachment_image_src( $upload_id );
			if ( ! empty( $image_attributes ) ) {
				$image_attributes = $image_attributes[0];
			} else {
				$image_attributes = '';
			}
			$res = array(
				'user_id' => $user->data->ID,
				'user_email' => $user->data->user_email,
				'user_nicename' => $user->data->user_nicename,
				'user_fname' => $users->first_name,
				'user_lname' => $users->last_name,
				'profile_url' => $image_attributes,
			);

			$response['data'] = $res;
			$response['message'] = 'User Login Successfully.';
		} else {
			// If user not found.
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'User not found. Check credentials';
		}

		return new WP_REST_Response( $response );
	}

	/**
	 * Post & Postmeta call back.
	 *
	 * @param WP_REST_Request $request Post request parameter.
	 */
	function gwl_rest_posts_metadata_endpoint_handler( WP_REST_Request $request ) {
		$response = array();
		$parameters = $request->get_params();

		$post_id   = isset( $parameters['post_id'] ) ? $parameters['post_id'] : '';
		$type      = isset( $parameters['post_type'] ) ? $parameters['post_type'] : '';
		$meta_keys = isset( $parameters['meta_keys'] ) ? $parameters['meta_keys'] : '';

		if ( ! empty( $post_id ) ) {
			$get_post_args = array(
				'posts_per_page' => 1,
				'include'        => $post_id,
				'post_type'      => $type,
				'post_status'    => 'publish',
			);
		} else {
			$get_post_args = array(
				'posts_per_page' => -1,
				'post_type'      => $type,
				'post_status'    => 'publish',
			);
		}

		$get_post_args = array_merge( $get_post_args, $this->gwl_get_meta_query_args( $meta_keys ) );

		$get_posts = get_posts( $get_post_args );
		if ( ! empty( $get_posts ) ) {
			$response['data'] = $get_posts;
			$response['status'] = 200;
			$response['message'] = 'Post Details';
		} else {
			$response['data'] = array();
			$response['status'] = 400;
			$response['message'] = 'No post found...';
		}

		return new WP_REST_Response( $response );
	}

	/**
	 * User & Usermeta call back.
	 *
	 * @param WP_REST_Request $request User request parameter.
	 */
	function gwl_rest_users_metadata_endpoint_handler( WP_REST_Request $request ) {
		$response   = array();
		$parameters = $request->get_params();

		$user_id   = isset( $parameters['user_id'] ) ? $parameters['user_id'] : '';
		$role      = isset( $parameters['role'] ) ? $parameters['role'] : '';
		$meta_keys = isset( $parameters['meta_keys'] ) ? $parameters['meta_keys'] : '';

		if ( ! empty( $user_id ) ) {
			$get_user_args = array(
				'number'   => -1,
				'include'  => $user_id,
				'role__in' => $role,
			);
		} else {
			$get_user_args = array(
				'number'   => -1,
				'role__in' => $role,
			);
		}

		$get_user_args = array_merge( $get_user_args, $this->gwl_get_meta_query_args( $meta_keys ) );

		$get_users = get_users( $get_user_args );

		if ( ! empty( $get_users ) ) {
			$record      = array();
			$record_data = array();
			foreach ( $get_users as $get_user ) {
				$record['ID']              = $get_user->data->ID;
				$record['user_login']      = $get_user->data->user_login;
				$record['user_nicename']   = $get_user->data->user_nicename;
				$record['user_email']      = $get_user->data->user_email;
				$record['user_registered'] = $get_user->data->user_registered;
				$record['display_name']    = $get_user->data->display_name;
				$record['roles']           = $get_user->roles;
				$record_data[]             = $record;
			}

			$response['data']    = $record_data;
			$response['status']  = 200;
			$response['message'] = 'User Details';
		} else {
			$response['data']    = array();
			$response['status']  = 400;
			$response['message'] = 'No user found...';
		}

		return new WP_REST_Response( $response );
	}

	/**
	 * Product & Product meta call back.
	 *
	 * @param WP_REST_Request $request Product request parameter.
	 */
	function gwl_rest_products_endpoint_handler( WP_REST_Request $request ) {
		$response   = array();

		$parameters = $request->get_params();
		
		$product_id = isset( $parameters['product_id'] ) ? $parameters['product_id'] : '';
		$meta_keys  = isset( $parameters['meta_keys'] ) ? $parameters['meta_keys'] : '';

		if ( ! empty( $product_id ) ) {
			$products = array(
				'posts_per_page' => 1,
				'include'        => $product_id,
				'post_type'      => 'product',
				'post_status'    => 'publish',
			);
		} else {
			$products = array(
				'posts_per_page' => -1,
				'post_type'      => 'product',
				'post_status'    => 'publish',
			);
		}

		$products = array_merge( $products, $this->gwl_get_meta_query_args( $meta_keys ) );

		$get_products = get_posts( $products );

		if ( ! empty( $get_products ) ) {
			$response['data']    = $get_products;
			$response['status']  = 200;
			$response['message'] = 'Product Details';
		} else {
			$response['data']    = array();
			$response['status']  = 400;
			$response['message'] = 'No product found...';
		}

		return new WP_REST_Response( $response );
	}
}

new GWL_Register_Route_API();

