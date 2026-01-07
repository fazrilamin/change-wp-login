<?php
/**
 * Plugin Name: Custom Login Slug (Permalink Setting)
 * Description: Change WordPress login URL and manage it under Settings → Permalinks.
 * Version: 1.0.0
 * Author: Fazril Amin
 */

if (!defined('ABSPATH')) exit;

/**
 * Add setting field to Permalink page
 */
add_action('admin_init', function () {

    register_setting('permalink', 'custom_login_slug');

    add_settings_field(
        'custom_login_slug',
        'Custom Login URL',
        function () {
            $slug = get_option('custom_login_slug', 'secure-login');
            echo '<input type="text" name="custom_login_slug" value="' . esc_attr($slug) . '" class="regular-text" />';
            echo '<p class="description">Example: <code>' . site_url('/') . '<strong>' . esc_html($slug) . '</strong></code></p>';
        },
        'permalink',
        'optional'
    );
});

/**
 * Block wp-login.php access
 */
add_action('init', function () {

    $slug = get_option('custom_login_slug', 'secure-login');

    if (
        strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false &&
        !is_user_logged_in()
    ) {
        wp_redirect(site_url('/' . $slug));
        exit;
    }
});

/**
 * Handle custom login slug
 */
add_action('template_redirect', function () {

    $slug = get_option('custom_login_slug', 'secure-login');
    $request = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    if ($request === $slug) {
        require_once ABSPATH . 'wp-login.php';
        exit;
    }
});

/**
 * Protect wp-admin for non-logged users
 */
add_action('admin_init', function () {

    if (!is_user_logged_in() && !defined('DOING_AJAX')) {
        $slug = get_option('custom_login_slug', 'secure-login');
        wp_redirect(site_url('/' . $slug));
        exit;
    }
});
