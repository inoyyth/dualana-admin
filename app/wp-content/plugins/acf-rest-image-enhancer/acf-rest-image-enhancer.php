<?php
/**
 * Plugin Name: ACF REST Image Enhancer
 * Author: Inoy Yth
 * Description: Convert ACF Image & Gallery fields into detailed objects for the REST API.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/ImageFormatter.php';

add_filter('acf/rest/format_value_for_rest', function (
    $value,
    $post_id,
    $field
) {
    return ACF_REST_Image_Formatter::format($value, $field);
}, 20, 3);