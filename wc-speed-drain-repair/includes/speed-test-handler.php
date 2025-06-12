<?php

    $has_subscriptions = is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php');
    $has_addons        = is_plugin_active('woocommerce-product-addons/woocommerce-product-addons.php');
    $has_bookings      = is_plugin_active('woocommerce-bookings/woocommerce-bookings.php');

$woo_asset_sizes = [
    'scripts' => [
        // Core Scripts
        'woocommerce'           => 120,
        'wc-add-to-cart'        => 15,
        'wc-order-attribution'  => 10,
        'sourcebuster-disable'  => 7,

        // Mini Cart Scripts
        'wc-mini-cart-block-frontend' => 45,
    ],
    'styles' => [
        // Mini Cart Styles
        'wc-blocks-style-mini-cart'        => 10,
        'wc-blocks-style-mini-cart-contents' => 20,

        // Block Styles
        'wc-blocks-style'                  => 60,
        'wc-blocks-style-customer-account' => 25,
        'wc-blocks-packages-style'         => 15,
        'woocommerce-blocktheme'           => 35,

        // General Styles
        'woocommerce-layout'      => 30,
        'brands-styles'           => 8,
        'woocommerce-smallscreen' => 12,
        'woocommerce-general'     => 40,
        'woocommerce-inline'      => 5,
        'woocommerce-coming-soon' => 6,
    ],
];

// Third-Party Plugin Assets (conditionally merged)
if ($has_subscriptions) {
    $woo_asset_sizes['scripts'] += [
        'wcs-checkout' => 25,
        'wcs-cart'     => 20,
    ];
    $woo_asset_sizes['styles'] += [
        'woocommerce-subscriptions' => 18,
        'wc-blocks-integration'     => 15,
    ];
}

if ($has_addons) {
    $woo_asset_sizes['scripts'] += [
        'woocommerce-addons'          => 22,
        'woocommerce-addons-frontend' => 20,
        'wc-addons-validation'        => 8,
    ];
    $woo_asset_sizes['styles'] += [
        'wc-addons-style' => 10,
    ];
}

if ($has_bookings) {
    $woo_asset_sizes['scripts'] += [
        'wc-bookings-frontend'   => 30,
        'wc-bookings-datepicker' => 18,
    ];
    $woo_asset_sizes['styles'] += [
        'wc-bookings-styles'   => 22,
        'wc-bookings-calendar' => 24,
    ];
}

add_action('wp_ajax_nw_speed_test', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'Unauthorized']);
    }

    $url = esc_url_raw($_POST['url'] ?? '');
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        wp_send_json_error(['error' => 'Invalid URL provided']);
    }

    // Example handles – ideally you should dynamically determine these based on enabled/disabled settings
    $enabled_assets = [
        'scripts' => array_keys($GLOBALS['woo_asset_sizes']['scripts']),
        'styles'  => array_keys($GLOBALS['woo_asset_sizes']['styles']),
    ];

    $disabled_assets = [
        'scripts' => [], // Simulate "disabled" by excluding handles
        'styles'  => [],
    ];

    $results = [];

    // 1. Normal request (assets enabled)
    $results['with_assets'] = nw_measure_speed($url, $enabled_assets);

    // 2. Request with assets disabled
    $url_without_assets = add_query_arg('woo_speed_test', 'off', $url);
    $results['without_assets'] = nw_measure_speed($url_without_assets, $disabled_assets);

    wp_send_json_success($results);
});

function nw_measure_speed($url, $included_assets = []) {
    global $woo_asset_sizes;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_NOBODY         => false,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (WooSpeedTest)',
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['error' => $error];
    }

    $info = curl_getinfo($ch);
    curl_close($ch);

    $total_size_kb = 0;
    foreach ($included_assets as $type => $handles) {
        foreach ($handles as $handle) {
            if (isset($woo_asset_sizes[$type][$handle])) {
                $total_size_kb += $woo_asset_sizes[$type][$handle];
            }
        }
    }

    return [
        'ttfb'    => round($info['starttransfer_time'] * 1000, 2),
        'total'   => round($info['total_time'] * 1000, 2),
        'size_kb' => round($total_size_kb, 2),
    ];
}
