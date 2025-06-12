<?php

// Dequeue selected WooCommerce assets based on toggles
add_action('wp_enqueue_scripts', 'repair_woocommerce_speed_dequeue_assets', PHP_INT_MAX);
function repair_woocommerce_speed_dequeue_assets() {
    if (!function_exists('is_woocommerce')) return;
    if (is_woocommerce() || is_cart() || is_checkout()) return;

    // Skip if test param is not present
    if (isset($_GET['woo_speed_test']) && $_GET['woo_speed_test'] === 'on') {
        return; // Skip dequeuing for the "with assets" test
    }

    $options = get_option('repair_woocommerce_speed_options', []);
    $script_handles = [
        'wc-mini-cart-block-frontend',
        'wc-add-to-cart',
        'woocommerce',
        'wc-order-attribution',
        
        // Third-party scripts
        'wcs-checkout',
        'wcs-cart',
        'wc-addon-script',
        'wc-addons-validation',
        'wc-bookings-frontend',
        'wc-bookings-datepicker',
        'woocommerce-addons',
        'woocommerce-addons-frontend',
    ];
    $style_handles = [
    // Core
    'wc-blocks-style',
    'wc-blocks-style-customer-account',
    'wc-blocks-style-mini-cart-contents',
    'wc-blocks-packages-style',
    'wc-blocks-style-mini-cart',
    'woocommerce-layout',
    'brands-styles',
    'woocommerce-smallscreen',
    'woocommerce-general',
    'woocommerce-blocktheme',
    'woocommerce-inline',
    'woocommerce-coming-soon',

    // Subscriptions
    'woocommerce-subscriptions',
    'wc-blocks-integration',

    // Add-Ons
    'wc-addons-style',

    // Bookings
    'wc-bookings-calendar',
    'wc-bookings-style',
    'wc-bookings-styles',
];

    foreach ($script_handles as $handle) {
        if (!empty($options[$handle]) && $options[$handle] === '1') {
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        }
    }
    foreach ($style_handles as $handle) {
        if (!empty($options[$handle]) && $options[$handle] === '1') {
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
    }
    
// Custom handles from user input
$custom_handles_raw = get_option('repair_woocommerce_speed_custom_handles', '');
$custom_handles = array_filter(array_map('trim', explode("\n", $custom_handles_raw)));

foreach ($custom_handles as $handle) {
    if (!empty($handle)) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }
}

}

add_action('wp_enqueue_scripts', function () {
    $options = get_option('repair_woocommerce_speed_options', []);
    if (
        !is_admin() &&
        !is_woocommerce() &&
        !is_cart() &&
        !is_checkout() &&
        !empty($options['sourcebuster-disable']) &&
        $options['sourcebuster-disable'] === '1'
    ) {
        wp_dequeue_script('sourcebuster-js');
        wp_deregister_script('sourcebuster-js');
    }
}, PHP_INT_MAX);
add_action('wp_ajax_save_woo_speed_setting', function () {
    check_ajax_referer('woo_speed_save_nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    $setting = sanitize_text_field($_POST['setting']);
    $value   = sanitize_text_field($_POST['value']);
    $options = get_option('repair_woocommerce_speed_options', []);
    $options[$setting] = $value;
    update_option('repair_woocommerce_speed_options', $options);
    wp_send_json_success('Saved');
});

// Disable sourcebuster.min.js from running whne toggle is on
add_action('template_redirect', function () {
    if (!function_exists('is_woocommerce')) return;
    $options = get_option('repair_woocommerce_speed_options', []);
    if (
        !is_admin() &&
        !is_woocommerce() &&
        !is_cart() &&
        !is_checkout() &&
        !empty($options['sourcebuster-disable']) &&
        $options['sourcebuster-disable'] === '1'
    ) {
        ob_start(function ($buffer) {
            return preg_replace(
                '#<script[^>]+sourcebuster\.min\.js[^>]*></script>#i',
                '',
                $buffer
            );
        });
    }
});