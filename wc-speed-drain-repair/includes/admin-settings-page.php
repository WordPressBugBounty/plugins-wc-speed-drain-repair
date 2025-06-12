<?php

// Add submenu under WooCommerce menu
add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce', 
        'Woo Speed Repair', 
        'Woo Speed Repair', 
        'manage_woocommerce', 
        'repair_woocommerce_speed_settings', 
        'repair_woocommerce_speed_render_settings_page'
    );
}, 99);
add_action('admin_menu', function () {
    remove_submenu_page('options-general.php', 'repair_woocommerce_speed_settings');
}, 100);

// Register settings page
add_action('admin_menu', function () {
    add_options_page(
        'WooCommerce Speed Repair Settings',
        'Woo Speed Repair',
        'manage_options',
        'repair_woocommerce_speed_settings',
        'repair_woocommerce_speed_render_settings_page'
    );
});

// Register plugin settings and custom handles field
add_action('admin_init', function () {
    // Register toggle settings
    register_setting('repair_woocommerce_speed_options', 'repair_woocommerce_speed_options');

    // Register custom handles field
    add_settings_section(
        'repair_woocommerce_speed_section',
        'Additional Settings',
        '__return_false',
        'repair_woocommerce_speed_settings'
    );

    add_settings_field(
        'repair_woocommerce_speed_custom_handles',
        'Custom Handles to Dequeue',
        function () {
            $value = get_option('repair_woocommerce_speed_custom_handles', '');
            echo '<textarea name="repair_woocommerce_speed_custom_handles" rows="6" cols="60" class="large-text code">' . esc_textarea($value) . '</textarea>';
            echo '<p class="description">Enter one handle per line. These styles/scripts will be dequeued.</p>';
        },
        'repair_woocommerce_speed_settings',
        'repair_woocommerce_speed_section'
    );

    register_setting(
        'repair_woocommerce_speed_settings',
        'repair_woocommerce_speed_custom_handles'
    );
});

// Render plugin settings page
function repair_woocommerce_speed_render_settings_page() {

    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    $has_subscriptions = is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php');
    $has_addons        = is_plugin_active('woocommerce-product-addons/woocommerce-product-addons.php');
    $has_bookings      = is_plugin_active('woocommerce-bookings/woocommerce-bookings.php');

    $options = get_option('repair_woocommerce_speed_options', []);
    $asset_groups = [
    'Core Scripts Assets' => [
        'woocommerce' => [
            'label' => 'WooCommerce Core Script',
            'desc'  => 'The main frontend script loaded by WooCommerce on all pages. It manages cart fragments, checkout behaviors, and other core interactive elements.',
        ],
        'wc-add-to-cart' => [
            'label' => 'Add to Cart Script',
            'desc'  => 'Handles AJAX-based add-to-cart functionality on product listings and single product pages. Often unnecessary on non-commerce pages.',
        ],
        'wc-order-attribution' => [
            'label' => 'Order Attribution Script',
            'desc'  => 'Captures order referral data such as campaign and traffic source, useful for tracking conversions across marketing channels.',
        ],
        'sourcebuster-disable' => [
            'label' => 'Sourcebuster Script',
            'desc'  => 'Third-party tracking library used by WooCommerce to help attribute order sources (like Google or Facebook). May be removed for privacy or speed.',
        ],
    ],
    'Mini Cart Assets' => [
        'wc-mini-cart-block-frontend' => [
            'label' => 'Mini Cart Block Script',
            'desc'  => 'Controls the behavior of the floating/cart drawer often shown in the header. Required if your theme uses the mini cart on all pages.',
        ],
        'wc-blocks-style-mini-cart' => [
            'label' => 'Mini Cart Wrapper Style',
            'desc'  => 'CSS for styling the outer container and visual wrapper of the mini cart block.',
        ],
        'wc-blocks-style-mini-cart-contents' => [
            'label' => 'Mini Cart Contents Style',
            'desc'  => 'CSS specifically targeting the product list, totals, and actions inside the mini cart dropdown or sidebar.',
        ],
    ],
    'Block Styles Assets' => [
        'wc-blocks-style' => [
            'label' => 'WC Blocks Style',
            'desc'  => 'Primary CSS loaded for WooCommerce Gutenberg blocks including product grids, filters, checkout, and more.',
        ],
        'wc-blocks-style-customer-account' => [
            'label' => 'Customer Account Block Style',
            'desc'  => 'Styles for the block-based customer account interface including login, registration, and dashboard areas.',
        ],
        'wc-blocks-packages-style' => [
            'label' => 'Block Packages Style',
            'desc'  => 'Shared styling for reusable WooCommerce block components used across multiple block types.',
        ],
        'woocommerce-blocktheme' => [
            'label' => 'Block Theme Style',
            'desc'  => 'Additional styles to support WooCommerce block themes. Only needed if using full-site editing or FSE-compatible themes.',
        ],
    ],
    'General Styles Assets' => [
        'woocommerce-layout' => [
            'label' => 'WooCommerce Layout CSS',
            'desc'  => 'Handles structural layout like columns, rows, and responsive behavior across WooCommerce templates.',
        ],
        'brands-styles' => [
    'label' => 'WooCommerce Brands CSS',
    'desc'  => 'Optional styles loaded by the WooCommerce Brands plugin, usually for brand logos or product labeling. Safe to disable on non-commerce pages.',
],
        'woocommerce-smallscreen' => [
            'label' => 'WooCommerce Smallscreen CSS',
            'desc'  => 'Loads extra styles optimized for mobile or tablet breakpoints. Can sometimes be combined into your theme styles.',
        ],
        'woocommerce-general' => [
            'label' => 'WooCommerce General CSS',
            'desc'  => 'Broad styling for buttons, notices, forms, and visual elements across WooCommerce pages.',
        ],
        'woocommerce-inline' => [
            'label' => 'Inline WooCommerce Styles',
            'desc'  => 'Inline CSS WooCommerce injects directly into the page head, often to support dynamic styles or theming.',
        ],
        'woocommerce-coming-soon' => [
            'label' => 'Coming Soon CSS',
            'desc'  => 'Optional styling for products or sections marked as “coming soon.” Rarely needed unless you’re using that feature.',
        ],
    ],
];

if ($has_subscriptions || $has_addons || $has_bookings) {
    $asset_groups['Third Party Plugin Assets'] = [];

    if ($has_subscriptions) {
        $asset_groups['Third Party Plugin Assets'] += [
    'woocommerce-subscriptions' => [
        'label' => 'WooCommerce Subscriptions CSS',
        'desc'  => 'Styles used by the WooCommerce Subscriptions plugin.',
    ],
    'wcs-checkout' => [
        'label' => 'WooCommerce Subscriptions Checkout JS',
        'desc'  => 'Handles checkout logic for subscription products.',
    ],
    'wcs-cart' => [
        'label' => 'WooCommerce Subscriptions Cart JS',
        'desc'  => 'Handles cart interaction for subscriptions.',
    ],
    'wc-blocks-integration' => [
        'label' => 'WooCommerce Subscriptions Blocks Integration CSS',
        'desc'  => 'Styles used to integrate Woo Subscriptions into WooCommerce Blocks (like Checkout Block).',
    ],
];
    }

    if ($has_addons) {
        $asset_groups['Third Party Plugin Assets'] += [
            'woocommerce-addons' => [
                'label' => 'WooCommerce Product Add-Ons Field Script',
                'desc'  => 'Handles dynamic fields for WooCommerce Product Add-Ons.',
            ],
            'woocommerce-addons-frontend' => [
                'label' => 'WooCommerce Product Add-Ons Frontend Script',
                'desc'  => 'Extra logic for frontend display of product add-ons and validation behavior.',
            ],
            'wc-addon-script' => [
                'label' => 'WooCommerce Product Add-Ons Core Script',
                'desc'  => 'Main JavaScript loaded by the Product Add-Ons plugin to initialize custom field logic and frontend behaviors. Disable this if you are not using add-on fields on the frontend.',
            ],
            'wc-addons-validation' => [
                'label' => 'WooCommerce Product Add-Ons Validation Script',
                'desc'  => 'Validates custom options added via Product Add-Ons.',
            ],
            'wc-addons-style' => [
                'label' => 'WooCommerce Product Add-Ons CSS',
                'desc'  => 'Styles used to display product add-ons on the frontend.',
            ],
        ];
    }

    if ($has_bookings) {
        $asset_groups['Third Party Plugin Assets'] += [
            'wc-bookings-frontend' => [
                'label' => 'WooCommerce Bookings Frontend JS',
                'desc'  => 'Manages booking slot logic and AJAX for availability.',
            ],
            'wc-bookings-datepicker' => [
                'label' => 'WooCommerce Bookings Datepicker JS',
                'desc'  => 'Enables the date/time picker for booking products.',
            ],
            'wc-bookings-style' => [
                'label' => 'WooCommerce Bookings Style',
                'desc'  => 'CSS for the WooCommerce Bookings frontend interface.',
            ],
            'wc-bookings-styles' => [
                'label' => 'WooCommerce Bookings Styles',
                'desc'  => 'CSS for the WooCommerce Bookings frontend interface.',
            ],
            'wc-bookings-calendar' => [
                'label' => 'WooCommerce Bookings Calendar CSS',
                'desc'  => 'Styles used for the calendar UI in bookings.',
            ],
        ];
    }
}
    ?>
<div id="woo-speed-toast" style="
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #00D78B;
    color: white;
    padding: 32px 33px;
    border-radius: 12px;
    font-size: 32px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    z-index: 9999;
">&#129395; Settings Saved</div>
    <div class="wrap" style="display: flex; gap: 30px;">
        <div style="flex: 3;">
            <h1 style="display: flex; align-items: center; gap: 12px; font-size: 24px;">
    <img src="<?php echo esc_url( plugins_url( '../assets/images/woo.png', __FILE__ ) ); ?>" style="height: 32px; width: auto;">
    WooCommerce Speed Repair Settings
</h1>
            <p style="font-size: 15px; line-height: 1.6; color: #222; margin-bottom: 0px;">
    These settings allow you to selectively disable specific WooCommerce styles and scripts that load across your site even when they’re not needed.
</p>
<p style="font-size: 15px; line-height: 1.6; color: #222; margin-bottom: 0px;">
By turning off unnecessary assets on non-WooCommerce pages, you can reduce page load time, decrease resource usage, and improve your site’s overall speed and performance.
</p>
<p style="font-size: 15px; line-height: 1.6; color: #222; margin-bottom: 0px;">
All options are safe to disable when you're not actively using those features on the frontend. <strong>This plugin will magicly detect when they are needed.</strong>
</p>
<hr style="margin-top: 10px; margin-bottom: 20px;">
<h2 style="font-size:22px; font-weight:600; color:#d16aff;">Savings Test Tool</h2>
<p style="font-size:14px;">Enter a full page URL to compare how it loads <strong>with and without WooCommerce assets</strong>. This helps you see how much your site improves when unneeded scripts and styles are disabled.
</p>
<div style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px;">
    <input type="text" id="nw_test_url" placeholder="<?php echo esc_url( get_site_url() ); ?>" 
        style="flex: 1; padding: 8px; font-size: 14px; font-weight:700" />
    <button id="nw_run_test" class="button button-primary" style="padding: 5px 15px; font-size: 18px;">&#128640; Run Savings Test
    </button>
</div>
<p id="nw_speed_test_note" style="margin-top: 15px; background: #fff8e1; border: 1px solid #ffd54f; padding: 12px 14px; border-radius: 6px; font-size: 15px; color: #222;">
    <strong>Note:</strong> Savings test results may vary slightly each time due to factors like server response time, caching behavior, background processes, and network latency.
</p>
<div id="nw_test_result" style="margin-top: 15px; font-size: 14px;"></div>
<?php include plugin_dir_path(__FILE__) . 'asset-summary.php'; ?>
            <form method="post" action="options.php">
                <?php settings_fields('repair_woocommerce_speed_options'); ?>
<div style="margin-top: 25px; margin-bottom: 30px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
    <button type="button" id="woo-speed-select-all" class="woo-speed-bulk-btn woo-speed-bulk-select">Disable All</button>
    <button type="button" id="woo-speed-deselect-all" class="woo-speed-bulk-btn woo-speed-bulk-deselect">Enable All</button>
    <div style="background: #fff8e1; border: 1px solid #ffd54f; padding: 8px 12px; border-radius: 6px; font-size: 13px; color: #222; flex: 1;">
        <strong>Note:</strong> When a toggle is <strong>ON</strong>, the corresponding asset is <strong>disabled</strong> on non-WooCommerce pages to improve performance.
    </div>
</div>
                <?php
$group_descriptions = [
    'Core Scripts Assets'     => 'Essential JavaScript files required for core WooCommerce functionality.',
    'Mini Cart Assets'        => 'Assets related to the WooCommerce mini cart block and styling. Keep these disabled if you are using a mini cart feature on all pages.',
    'Block Styles Assets'     => 'Styles used for WooCommerce Gutenberg blocks and components.',
    'General Styles Assets'   => 'Additional layout and theme styles that WooCommerce loads sitewide.',   
    'Third Party Plugin Assets' => 'Assets loaded by popular WooCommerce extensions like Subscriptions, Bookings, and Product Add-Ons. Disable any that aren’t needed sitewide to improve performance.',
];
foreach ($asset_groups as $group_title => $group_assets): ?>
    <h2 style="margin-top:30px; font-size:23px; font-weight:600; color:#d16aff;"><?php echo esc_html($group_title); ?></h2>
    <?php
$desc = $group_descriptions[$group_title] ?? '';
$highlight = '';
if ($group_title === 'Mini Cart Assets') {
    $desc_parts = explode('Keep these disabled', $desc);
    $highlight = '<span style="color: red;font-weight:700">
        Keep these toggled off if you are using a mini cart feature on the site.
    </span>';
    $desc = trim($desc_parts[0]);
}
?>
<p style="margin-top: -10px; font-size: 16px; color: #222; margin-bottom: 10px;">
    <?php echo esc_html($desc); ?><?php echo $highlight; ?>
</p>
    <?php foreach ($group_assets as $handle => $asset):
        $label   = $asset['label'];
        $desc    = $asset['desc'];
        $checked = isset($options[$handle]) && $options[$handle] === '1';
    ?>
        <div class="woo-speed-toggle-row">
            <div class="woo-speed-label-container">
                <label class="woo-speed-toggle-label <?php echo $checked ? 'disabled' : ''; ?>" for="<?php echo esc_attr($handle); ?>">
                    <?php echo esc_html($label); ?>
                </label>
                <div class="woo-speed-desc"><?php echo esc_html($desc); ?></div>
            </div>
            <label class="woo-speed-switch">
                <input type="checkbox"
                       id="<?php echo esc_attr($handle); ?>"
                       name="repair_woocommerce_speed_options[<?php echo esc_attr($handle); ?>]"
                       value="1"
                       <?php checked($checked); ?>
                       onchange="this.closest('.woo-speed-toggle-row').querySelector('.woo-speed-toggle-label').classList.toggle('disabled', this.checked);" />
                <span class="woo-speed-slider"></span>
            </label>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
                <?php //submit_button(); ?>
</form>

<!-- Custom Handles Dequeue Form -->
<form method="post" action="options.php" style="margin-top: 40px;">
    <h2 style="font-size: 20px; font-weight: 600;">Custom Handles to Dequeue</h2>
    <p>Enter one script or style handle per line to be dequeued on the frontend.</p>
    <?php
        settings_fields('repair_woocommerce_speed_settings');
        do_settings_sections('repair_woocommerce_speed_settings');
        submit_button('Save Custom Handles');
    ?>
            </form>
            </div>
        <div style="flex: 1; border-left: 1px solid #ccc; padding-left: 20px;max-width: 444px;margin-top: 55px;">
            <style>.settings-sidebar {background: #444 url('<?php echo esc_url( plugins_url( '../assets/images/box-bg.png', __FILE__ ) ); ?>') no-repeat center center;}</style>
<div class="settings-sidebar">
    <a href="https://www.wpfixit.com" target="_blank" class="wpfi-hover-raise">
        <img src="<?php echo esc_url( plugins_url( '../assets/images/wpfi-logo.webp', __FILE__ ) ); ?>" alt="WP Fix It" style="max-width: 150px; height: auto;" loading="lazy" decoding="async">
    </a>
    <p>This plugin is brought to you by WP Fix It.<br>Experts in instant WordPress support!</p>
    <a href="https://www.wpfixit.com/save/20-off/?ref=1" target="_blank" class="wpfi-hover-raise">
        <img src="<?php echo esc_url( plugins_url( '../assets/images/save.png', __FILE__ ) ); ?>" alt="WP Fix It - WordPress Experts" style="border-radius: 12px; width: 325px;" title="20% Off WP Fix It's Services" loading="lazy" decoding="async">
    </a>
</div>
<div class="settings-sidebar">
    <h2 style="margin-top:15px; font-size:25px; font-weight:700; color:#d16aff;">Speed Testing Tool</h2>
    <p>Instantly check your website’s speed and performance, identifying areas for improvement to ensure fast loading times and a seamless experience for all users.</p>
    <a href="https://www.wpfixit.com/tools/speed-check/" target="_blank" class="wpfi-hover-raise">
        <img src="<?php echo esc_url( plugins_url( '../assets/images/speed-check.png', __FILE__ ) ); ?>" alt="WP Fix It - WordPress Experts" style="border-radius: 12px; width: 325px;" title="Speed Testing Tool" loading="lazy" decoding="async">
    </a>
</div>
<div class="settings-sidebar">
    <h2 style="margin-top:15px; font-size:25px; font-weight:700; color:#d16aff;">WordPress Inspector Tool</h2>
    <p>Quickly analyze any WordPress site revealing details about its themes, plugins, security, speed and core installation.</p>
    <a href="https://www.wpfixit.com/tools/wp-inspector/" target="_blank" class="wpfi-hover-raise">
        <img src="<?php echo esc_url( plugins_url( '../assets/images/wp-inspector.png', __FILE__ ) ); ?>" alt="WP Fix It - WordPress Experts" style="border-radius: 12px; width: 325px;" title="WordPress Inspector Tool" loading="lazy" decoding="async">
    </a>
</div>
<div class="settings-sidebar">
    <h2 style="margin-top:15px; font-size:25px; font-weight:700; color:#d16aff;">Device View Tool</h2>
    <p>Easily preview how a website looks on multiple devices, ensuring responsiveness and consistency across different screen sizes.</p>
    <a href="https://www.wpfixit.com/tools/device-view/" target="_blank" class="wpfi-hover-raise">
        <img src="<?php echo esc_url( plugins_url( '../assets/images/device-view.png', __FILE__ ) ); ?>" alt="WP Fix It - WordPress Experts" style="border-radius: 12px; width: 325px;" title="Device View Tool" loading="lazy" decoding="async">
    </a>
</div>
<div class="settings-sidebar">
    <h2 style="margin-top:15px; font-size:25px; font-weight:700; color:#d16aff;">Domain Detective Tool</h2>
    <p>Accessing details about a domain's hosting, server, DNS records, health, speed and how it looks across multiple devices is quick and easy.</p>
    <a href="https://www.wpfixit.com/tools/domain-detective/" target="_blank" class="wpfi-hover-raise">
        <img src="<?php echo esc_url( plugins_url( '../assets/images/domain-detective.png', __FILE__ ) ); ?>" alt="WP Fix It - WordPress Experts" style="border-radius: 12px; width: 325px;" title="Device View Tool" loading="lazy" decoding="async">
    </a>
</div>
        </div>
    </div>
    <?php
}