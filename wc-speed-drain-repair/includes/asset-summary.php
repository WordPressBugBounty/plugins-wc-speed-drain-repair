<?php
$all_assets = [
    'Core Scripts' => ['woocommerce', 'wc-add-to-cart', 'wc-order-attribution', 'sourcebuster-disable'],
    'Mini Cart' => ['wc-mini-cart-block-frontend', 'wc-blocks-style-mini-cart', 'wc-blocks-style-mini-cart-contents'],
    'Block Styles' => ['wc-blocks-style', 'wc-blocks-style-customer-account', 'wc-blocks-packages-style', 'woocommerce-blocktheme'],
    'General Styles' => ['woocommerce-layout', 'brands-styles', 'woocommerce-smallscreen', 'woocommerce-general', 'woocommerce-inline', 'woocommerce-coming-soon'],
];

$asset_sizes_kb = [
    'woocommerce' => 55,
    'wc-add-to-cart' => 15,
    'wc-order-attribution' => 9,
    'sourcebuster-disable' => 12,
    'wc-mini-cart-block-frontend' => 18,
    'wc-blocks-style-mini-cart' => 14,
    'wc-blocks-style-mini-cart-contents' => 11,
    'wc-blocks-style' => 20,
    'wc-blocks-style-customer-account' => 13,
    'wc-blocks-packages-style' => 17,
    'woocommerce-blocktheme' => 19,
    'woocommerce-layout' => 25,
    'brands-styles' => 10,
    'woocommerce-smallscreen' => 8,
    'woocommerce-general' => 30,
    'woocommerce-inline' => 6,
    'woocommerce-coming-soon' => 7,
];

$options = get_option('repair_woocommerce_speed_options', []);

$disabled_assets = [];
$active_assets = [];
$group_data = [];
$total_assets = 0;

foreach ($all_assets as $group => $handles) {
    $group_disabled_count = 0;

    foreach ($handles as $handle) {
        $is_disabled = isset($options[$handle]) && $options[$handle] === '1';

        if ($is_disabled) {
            $disabled_assets[] = $handle;
            $group_disabled_count++;
        } else {
            $active_assets[] = $handle;
        }
    }

    $group_data[$group] = $group_disabled_count;
    $total_assets += count($handles);
}

$disabled_count = count($disabled_assets);
$still_active = count($active_assets);

// Estimate size and time savings
$estimated_kb_saved = 0;
foreach ($disabled_assets as $handle) {
    $estimated_kb_saved += $asset_sizes_kb[$handle] ?? 0;
}
$estimated_time_saved = round(($estimated_kb_saved / 100) * 0.2, 2);

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

        <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 5px; color: #333;">
            <p style="margin-top: 7px !important;font-size: 23px;margin: 0px;"><strong><span style="font-size: 15px;">Estimated non-WooCommerce Page Size Saved =</span> <?php echo $estimated_kb_saved; ?> KB</strong></p>
            <p style="font-size: 23px;margin: 0px;"><strong><span style="font-size: 15px;">Estimated non-WooCommerce Page Load Time Saved =</span> <?php echo $estimated_time_saved; ?> Seconds</strong></p>
        </div>
    </div>

    <?php if ($has_active_assets) : ?>
    <div style="flex: 1; background: #fae6e8; padding: 10px 30px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.05);margin-bottom:15px;">
        <h3 style="font-size: 23px; margin-bottom: 10px;color:#222">⚠️ Assets Still Active </h3>
        <ul style="list-style: none; font-size: 15px; color: #333; line-height: 1.6; margin-left:33px">
            <?php foreach ($active_assets as $asset) : ?>
                <li>- <strong><?php echo esc_html($asset); ?></strong></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>
