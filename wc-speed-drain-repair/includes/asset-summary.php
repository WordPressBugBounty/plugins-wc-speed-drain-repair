<?php
include_once ABSPATH . 'wp-admin/includes/plugin.php';

$has_subscriptions = is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php');
$has_addons        = is_plugin_active('woocommerce-product-addons/woocommerce-product-addons.php');
$has_bookings      = is_plugin_active('woocommerce-bookings/woocommerce-bookings.php');

$all_assets = [
    'Core Scripts' => ['woocommerce', 'wc-add-to-cart', 'wc-order-attribution', 'sourcebuster-disable'],
    'Mini Cart' => ['wc-mini-cart-block-frontend', 'wc-blocks-style-mini-cart', 'wc-blocks-style-mini-cart-contents'],
    'Block Styles' => ['wc-blocks-style', 'wc-blocks-style-customer-account', 'wc-blocks-packages-style', 'woocommerce-blocktheme'],
    'General Styles' => ['woocommerce-layout', 'brands-styles', 'woocommerce-smallscreen', 'woocommerce-general', 'woocommerce-inline', 'woocommerce-coming-soon'],
];
if ($has_subscriptions || $has_addons || $has_bookings) {
    $all_assets['Third Party Assets'] = [];

    if ($has_subscriptions) {
        $all_assets['Third Party Assets'] = array_merge(
            $all_assets['Third Party Assets'],
            ['woocommerce-subscriptions', 'wcs-checkout', 'wcs-cart', 'wc-blocks-integration']
        );
    }

    if ($has_addons) {
        $all_assets['Third Party Assets'] = array_merge(
            $all_assets['Third Party Assets'],
            ['wc-addon-script', 'wc-addons-validation', 'wc-addons-style', 'woocommerce-addons-frontend']
        );
    }

    if ($has_bookings) {
        $all_assets['Third Party Assets'] = array_merge(
            $all_assets['Third Party Assets'],
            ['wc-bookings-frontend', 'wc-bookings-datepicker', 'wc-bookings-style', 'wc-bookings-styles', 'wc-bookings-calendar']
        );
    }
}

$options = get_option('repair_woocommerce_speed_options', []);
$custom_handles_raw = get_option('repair_woocommerce_speed_custom_handles', '');
$custom_handles = array_filter(array_map('trim', explode("\n", $custom_handles_raw)));

if (!empty($custom_handles)) {
    $all_assets['Custom Handles'] = $custom_handles;
}
$disabled_assets = [];
$active_assets = [];
$group_data = [];
$total_assets = 0;

// Remove custom handles from other groups to avoid double-counting
foreach ($all_assets as $group => $handles) {
    $group_disabled_count = 0;

    foreach ($handles as $handle) {
        $was_disabled_by_toggle = isset($options[$handle]) && $options[$handle] === '1';
        $was_disabled_by_custom = in_array($handle, $custom_handles, true);

        $is_disabled = $was_disabled_by_toggle || $was_disabled_by_custom;

        // Count the asset under its group if:
        // - It's a custom handle and in the "Custom Handles" group
        // - OR it's toggled off and belongs to its non-custom group
        $should_count = (
            ($group === 'Custom Handles' && $was_disabled_by_custom) ||
            ($group !== 'Custom Handles' && $was_disabled_by_toggle)
        );

        if ($is_disabled) {
            $disabled_assets[] = $handle;
        } else {
            $active_assets[] = $handle;
        }

        if ($should_count) {
            $group_disabled_count++;
        }
    }

    if ($group_disabled_count > 0) {
        $group_data[$group] = $group_disabled_count;
    }

    $total_assets += count($handles);
}

$disabled_count = count($disabled_assets);
$still_active = count($active_assets);

wp_localize_script('woo-speed-charts-js', 'wooAssetChartData', [
    'pie' => [$disabled_count, $still_active],
    'bar' => [
        'labels' => array_keys($group_data),
        'values' => array_values($group_data)
    ]
]);

$has_active_assets = $still_active > 0;
?>

<div style="display: flex; gap: 30px; margin-top: 40px; flex-wrap: wrap;">
    <div style="<?php echo $has_active_assets ? 'flex: 2;' : 'flex: 1 1 100%;'; ?> background: #fff; padding: 30px 30px 15px; margin-bottom:15px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.05);">
        <h2 style="margin-top: 0; color: #d16aff;font-size: 23px;">Optimization Summary</h2>

        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <canvas id="wooAssetPie" width="300" height="300"></canvas>
            </div>
            <div style="flex: 1; min-width: 250px;">
                <canvas id="wooAssetBar" width="300" height="300"></canvas>
            </div>
        </div>
</div>
    <?php if ($has_active_assets) : ?>
    <div style="flex: 1; background: #fae6e8; padding: 10px 30px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.05);margin-bottom:15px;">
        <h3 style="font-size: 21px; margin-bottom: 10px;color:#222">⚠️ Assets Still Active Gloablly </h3>
        <ul style="list-style: none; font-size: 15px; color: #333; line-height: 1.6; margin-left:33px">
            <?php foreach ($active_assets as $asset) : ?>
                <li><strong><?php echo esc_html($asset); ?></strong></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>
