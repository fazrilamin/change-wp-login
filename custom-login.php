<?php
/**
 * Plugin Name: Custom Login Slug (Permalink Setting)
 * Description: Change WordPress login URL and manage it under Settings → Permalinks.
 * Version: 1.0.3
 * Author: Fazril Amin
 */

if (!defined('ABSPATH')) exit;

/**
 * Render field on Permalink page
 */
add_action('admin_init', function () {

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
 * Manually save value from Permalink page
 */
add_action('load-options-permalink.php', function () {

    if (!current_user_can('manage_options')) return;

    if (isset($_POST['custom_login_slug'])) {
        check_admin_referer('update-permalink');

        $slug = sanitize_title($_POST['custom_login_slug']);
        if (empty($slug)) {
            $slug = 'secure-login';
        }

        update_option('custom_login_slug', $slug);
    }
});

/**
 * Return 404 for wp-admin when NOT logged in
 */
add_action('init', function () {

    if (
        !is_user_logged_in() &&
        preg_match('#^/wp-admin/?#', $_SERVER['REQUEST_URI'])
    ) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        exit;
    }
});

/**
 * Block wp-login.php access
 */
add_action('init', function () {

    if (
        !is_user_logged_in() &&
        strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false
    ) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
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
