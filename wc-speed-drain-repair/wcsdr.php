<?php
/*
Plugin Name: WC Speed Repair
Plugin URI: https://www.wpfixit.com
Description: Optimize WooCommerce performance by disabling selected frontend assets that aren't needed on non-commerce pages. This plugin reduces load times and server resource usage by letting you control exactly which styles and scripts WooCommerce loads. Ideal for speeding up high-traffic or resource-heavy WooCommerce sites, with instant toggle-based settings and no coding required.
Version: 4.5
Author: WP Fix It - WordPress Experts
Author URI: https://www.wpfixit.com
Requires Plugins: woocommerce
Requires at least: 5.6
Requires PHP: 7.4
License: GPL2
*/
if (!defined('ABSPATH')) exit;

// Load up required files
require_once plugin_dir_path(__FILE__) . 'includes/enqueue-admin-assets.php';
enqueue_woo_admin_assets(__FILE__);
require_once plugin_dir_path(__FILE__) . 'includes/asset-control.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-bar-assets.php';
require_once plugin_dir_path(__FILE__) . 'includes/speed-test-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/per-page-dequeue.php';


// Set option in database on activation
register_activation_hook(__FILE__, function () {
    // Redirect logic
    set_transient('_repair_woocommerce_speed_do_redirect', true, 30);

    // Default settings: enable all toggles
    $default_options = [
        // Core Scripts
        'woocommerce' => '1',
        'wc-add-to-cart' => '1',
        'wc-order-attribution' => '1',
        'sourcebuster-disable' => '1',

        // Mini Cart
        'wc-mini-cart-block-frontend' => '1',
        'wc-blocks-style-mini-cart' => '1',
        'wc-blocks-style-mini-cart-contents' => '1',

        // Block Styles
        'wc-blocks-style' => '1',
        'wc-blocks-style-customer-account' => '1',
        'wc-blocks-packages-style' => '1',
        'woocommerce-blocktheme' => '1',

        // General Styles
        'woocommerce-layout' => '1',
        'brands-styles' => '1',
        'woocommerce-smallscreen' => '1',
        'woocommerce-general' => '1',
        'woocommerce-inline' => '1',
        'woocommerce-coming-soon' => '1',
    ];

    update_option('repair_woocommerce_speed_options', $default_options);
});

// Redirect to settings page
add_action('admin_init', function () {
    if (get_transient('_repair_woocommerce_speed_do_redirect')) {
        delete_transient('_repair_woocommerce_speed_do_redirect');
        wp_safe_redirect(admin_url('admin.php?page=repair_woocommerce_speed_settings'));
        exit;
    }
});

// Add settings link in plugin list
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $settings_link = '<a href="https://www.wpfixit.com/improve-wordpress-speed-and-performance" target="_blank" style="font-weight:bold;color:#d16aff;">GET SPEED</a>';
    array_unshift($links, $settings_link);
    return $links;
});

// DEBUG FUNCTIONS BELOW ONLY
