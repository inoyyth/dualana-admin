<?php
/**
 * Plugin Name: Advanced WP REST API
 * Plugin URI: https://wordpress.org/plugins/advanced-wp-rest-api/
 * Description: This plugin register multiple REST API endpoints
 * Version: 1.4
 * Author: galaxyweblinks
 * Author URI: https://profiles.wordpress.org/galaxyweblinks/#content-plugins
 * License: GPL3
 * Text Domain: advanced-wp-rest-api
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'apis/class-gwl-register-route-api.php';

add_action( 'admin_enqueue_scripts', 'awpr_callback_for_setting_up_scripts' );
function awpr_callback_for_setting_up_scripts( $hook_suffix ) {
	if ( 'settings_page_awpr_settings' !== $hook_suffix ) {
		return;
	}
	wp_enqueue_style( 'awpr-custom-css', plugins_url( 'assets/css/custom.css', __FILE__ ), array(), '1.4.0', 'all' );
}

add_action( 'admin_init', 'awpr_register_plugin_settings' );
function awpr_register_plugin_settings() {
	$checkbox_args = array(
		'type'              => 'string',
		'sanitize_callback' => 'awpr_sanitize_checkbox_setting',
		'default'           => '',
	);

	register_setting( 'awpr-plugin-settings-group', 'awpr_user_login_api', $checkbox_args );
	register_setting( 'awpr-plugin-settings-group', 'awpr_post_api', $checkbox_args );
	register_setting( 'awpr-plugin-settings-group', 'awpr_user_api', $checkbox_args );
	register_setting( 'awpr-plugin-settings-group', 'awpr_product_api', $checkbox_args );
}

/**
 * Sanitize checkbox settings: store "yes" when enabled, empty string when disabled.
 *
 * @param mixed $value Submitted option value.
 * @return string
 */
function awpr_sanitize_checkbox_setting( $value ) {
	return ( 'yes' === $value ) ? 'yes' : '';
}

function awpr_register_options_page() {
	add_options_page( 'AWPR Settings', 'Advanced WP REST API', 'manage_options', 'awpr_settings', 'awpr_options_page' );
}
add_action( 'admin_menu', 'awpr_register_options_page' );

function awpr_options_page() {
?>
    <div class="wrap awpr_main">
        <h2><?php esc_html_e( 'Enable/Disable Routes', 'advanced-wp-rest-api' ); ?></h2>
        
        <div class="notice awpr--notice">
            <div>
                <h3><?php esc_html_e( 'Advanced WP REST API', 'advanced-wp-rest-api' ); ?></h3>
                <p>Here's a link to the documentation for the plugin. This will help you learn more about its features and how to use it.</p>
                <div class="e-notice__actions">
                    <a href="https://wp-plugins.galaxyweblinks.com/wp-plugins/advanced-wp-rest-api/doc/" class="e-button--cta cta-secondary" target="_blank"><span>Documentation</span></a>
                </div>
                <p class="e-note">For any feedback or queries regarding this plugin, please contact our <a href="https://wp-plugins.galaxyweblinks.com/contact/" target="_blank">Support team</a>.</p>
            </div>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields( 'awpr-plugin-settings-group' ); ?>
            <?php do_settings_sections( 'awpr-plugin-settings-group' ); ?>
            <table>
                <tr valign="top" class="awpr-api-table">
                    <th scope="row"><label for="awpr_user_login_api"><?php esc_html_e( 'Login API', 'advanced-wp-rest-api' ); ?></label></th>
                    <td>
                        <input type="hidden" name="awpr_user_login_api" value="" />
                        <input type="checkbox" id="awpr_user_login_api" name="awpr_user_login_api" value="yes" <?php checked( get_option( 'awpr_user_login_api' ), 'yes' ); ?> />
                        <p><?php esc_html_e( 'Please check if you want to enable the Login API', 'advanced-wp-rest-api' ); ?></p>
                    </td>
                </tr>
                <tr valign="top" class="awpr-api-table">
                    <th scope="row"><label for="awpr_post_api"><?php esc_html_e( 'Post API', 'advanced-wp-rest-api' ); ?></label></th>
                    <td>
                        <input type="hidden" name="awpr_post_api" value="" />
                        <input type="checkbox" id="awpr_post_api" name="awpr_post_api" value="yes" <?php checked( get_option( 'awpr_post_api' ), 'yes' ); ?> />
                        <p><?php esc_html_e( 'Please check if you want to enable the Post API', 'advanced-wp-rest-api' ); ?></p>
                    </td>
                </tr>
                <tr valign="top" class="awpr-api-table">
                    <th scope="row"><label for="awpr_user_api"><?php esc_html_e( 'User API', 'advanced-wp-rest-api' ); ?></label></th>
                    <td>
                        <input type="hidden" name="awpr_user_api" value="" />
                        <input type="checkbox" id="awpr_user_api" name="awpr_user_api" value="yes" <?php checked( get_option( 'awpr_user_api' ), 'yes' ); ?> />
                        <p><?php esc_html_e( 'Please check if you want to enable the User API', 'advanced-wp-rest-api' ); ?></p>
                    </td>
                </tr>
                <tr valign="top" class="awpr-api-table">
                    <th scope="row"><label for="awpr_product_api"><?php esc_html_e( 'Product API', 'advanced-wp-rest-api' ); ?></label></th>
                    <td>
                        <input type="hidden" name="awpr_product_api" value="" />
                        <input type="checkbox" id="awpr_product_api" name="awpr_product_api" value="yes" <?php checked( get_option( 'awpr_product_api' ), 'yes' ); ?> />
                        <p><?php esc_html_e( 'Please check if you want to enable the Product API', 'advanced-wp-rest-api' ); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

/*
*  Plugin Setting Link 
*/
function awpr_settings_link( $links, $plugin_file ) {
    if( plugin_basename( __FILE__ ) == $plugin_file ){
        $settings_ui_links = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=awpr_settings' ), __( 'Settings', 'advanced-wp-rest-api' ) );
        array_unshift( $links, $settings_ui_links );
    }
    return $links;
}
add_filter( 'plugin_action_links', 'awpr_settings_link', 10, 2 );

/**
 * You can use these filters to add custom links to your plugin row in the plugin list.
 * @param $links, $file
 * @return $links [array]
 */
function awpr_add_custom_plugin_links($links, $file) {
	if ($file === 'advanced-wp-rest-api/advanced-wp-rest-api.php') {
		$links[] = '<a href="https://wp-plugins.galaxyweblinks.com/wp-plugins/advanced-wp-rest-api/doc/" target="_blank">Documentation</a>';
		$links[] = '<a href="https://wp-plugins.galaxyweblinks.com/contact/" target="_blank">Contact Support</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'awpr_add_custom_plugin_links', 10, 2);