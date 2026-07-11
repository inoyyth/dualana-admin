<?php
/**
 * Plugin Name: API Key Authentication
 * Plugin URI: https://example.com
 * Description: Protect WordPress REST API using an API Key.
 * Version: 1.0.0
 * Author: Inoyyth
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit;
}

class ApiKeyAuthentication
{
    /**
     * Header name
     */
    private string $header = 'HTTP_X_API_KEY';

    /**
     * Endpoints that don't require an API key.
     */
    private array $whitelist = [
        '/',
    ];

    public function __construct()
    {
        add_filter(
            'rest_authentication_errors',
            [$this, 'authenticate'],
            99
        );
    }

    /**
     * Authenticate REST API requests.
     */
    public function authenticate($result)
    {
        if (!empty($result)) {
            return $result;
        }

        $request = rest_get_server()->get_raw_data();

        $route = $_SERVER['REQUEST_URI'] ?? '';

        foreach ($this->whitelist as $item) {
            if (str_ends_with($route, '/wp-json' . $item) ||
                str_ends_with($route, '/wp-json' . $item . '/')) {
                return $result;
            }
        }

        $clientKey = $_SERVER[$this->header] ?? '';

        $serverKey = MY_API_KEY;

        if (!$serverKey) {
            return new WP_Error(
                'api_key_missing',
                'Server API key is not configured.',
                ['status' => 500]
            );
        }

        if (!hash_equals($serverKey, $clientKey)) {
            return new WP_Error(
                'invalid_api_key',
                'Invalid API Key.',
                ['status' => 401]
            );
        }

        return $result;
    }
}

new ApiKeyAuthentication();