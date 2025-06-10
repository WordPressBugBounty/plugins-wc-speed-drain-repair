<?php
/*
Plugin Name: WooCommerce Speed Repair
Plugin URI: https://www.wpfixit.com
Description: Optimize WooCommerce performance by disabling selected frontend assets that aren't needed on non-commerce pages. This plugin reduces load times and server resource usage by letting you control exactly which styles and scripts WooCommerce loads. Ideal for speeding up high-traffic or resource-heavy WooCommerce sites, with instant toggle-based settings and no coding required.
Version: 4.2
Author: WP Fix It - WordPress Experts
Author URI: https://www.wpfixit.com
Requires Plugins: woocommerce
Requires at least: 5.6
Requires PHP: 7.4
License: GPL2
*/
if (!defined('ABSPATH')) exit;
// Load up admin styles
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'woocommerce_page_repair_woocommerce_speed_settings') {
        wp_enqueue_style(
            'repair-woo-speed-admin-css',
            plugins_url('assets/css/admin-settings.css', __FILE__),
            [],
            '1.0'
        );
    }
});
// Load up admin scripts
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'woocommerce_page_repair_woocommerce_speed_settings') {
        // CSS
        $css_file = plugin_dir_path(__FILE__) . 'assets/admin-settings.css';
        $css_url  = plugins_url('assets/admin-settings.css', __FILE__);
        $css_ver  = file_exists($css_file) ? filemtime($css_file) : time();
        wp_enqueue_style('repair-woo-speed-admin-css', $css_url, [], $css_ver);
        // JS
        $js_file = plugin_dir_path(__FILE__) . 'assets/js/admin-settings.js';
        $js_url  = plugins_url('assets/js/admin-settings.js', __FILE__);
        $js_ver  = file_exists($js_file) ? filemtime($js_file) : time();
        wp_enqueue_script('repair-woo-speed-admin-js', $js_url, [], $js_ver, true);
        // Localize nonce
        wp_localize_script('repair-woo-speed-admin-js', 'wooSpeedSettings', [
            'nonce' => wp_create_nonce('woo_speed_save_nonce'),
        ]);
    }
});
// Redirect to settings page after activation
register_activation_hook(__FILE__, function () {
    set_transient('_repair_woocommerce_speed_do_redirect', true, 30);
});
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
add_action('admin_menu', function () {
    // Add submenu under WooCommerce menu
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
// Register plugin settings
add_action('admin_init', function () {
    register_setting('repair_woocommerce_speed_options', 'repair_woocommerce_speed_options');
});
// Render plugin settings page
function repair_woocommerce_speed_render_settings_page() {
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
            'desc'  => 'Optional styling for products or sections marked as &#8220;coming soon.&#8221; Rarely needed unless you&#8217;re using that feature.',
        ],
    ],
];
    ?>
    <div id="woo-speed-toast" style="
    display: none;
    position: fixed;
    bottom: 30px;
    right: 30px;
    background-color: #00D78B;
    color: white;
    padding: 12px 20px;
    border-radius: 6px;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    z-index: 9999;
">Setting Saved</div>
    <div class="wrap" style="display: flex; gap: 30px;">
        <div style="flex: 3;">
            <h1 style="display: flex; align-items: center; gap: 12px; font-size: 24px;">
    <img src="<?php echo plugins_url('assets/images/woo.png', __FILE__); ?>" style="height: 32px; width: auto;">
    WooCommerce Speed Repair Settings
</h1>
            <p style="font-size: 15px; line-height: 1.6; color: #555; margin-bottom: 0px;">
    These settings allow you to selectively disable specific WooCommerce styles and scripts that load across your site even when they&#8217;re not needed. By turning off unnecessary assets on non-WooCommerce pages, you can reduce page load time, decrease resource usage, and improve your site&#8217;s overall speed and performance. All options are safe to disable when you're not actively using those features on the frontend.
</p><div class="wrap" style="display: flex; gap: 30px;"><div style="flex: 3;">
<img src="<?php echo plugins_url('assets/images/info-1.png', __FILE__); ?>" style="height: 100%;max-width: 100%;margin: 0 auto;display: block;" loading="lazy" decoding="async">
</div>
<div style="flex: 3;">
<img src="<?php echo plugins_url('assets/images/info-2.png', __FILE__); ?>" style="height: 100%;max-width: 100%;margin: 0 auto;display: block;" loading="lazy" decoding="async">
</div>
<div style="flex: 3;">
<img src="<?php echo plugins_url('assets/images/info-3.png', __FILE__); ?>" style="height: 100%;max-width: 100%;margin: 0 auto;display: block;" loading="lazy" decoding="async">
</div>
</div>
<div style="background: #fff8c4;color: #444;border: 1px solid #ffe58f;padding: 8px 12px;border-radius: 4px;margin-top: 8px;font-size: 14px;">
    <strong>Note:</strong> When a toggle is <strong>ON</strong>, the corresponding asset is <strong>disabled</strong> on non-WooCommerce pages to improve performance.
</div>
            <form method="post" action="options.php">
                <?php settings_fields('repair_woocommerce_speed_options'); ?>
<div style="margin-top: 25px; margin-bottom: 30px; display: flex; gap: 12px;">
    <button type="button" id="woo-speed-select-all" class="woo-speed-bulk-btn woo-speed-bulk-select">Disable All</button>
    <button type="button" id="woo-speed-deselect-all" class="woo-speed-bulk-btn woo-speed-bulk-deselect">Enable All</button>
</div>
                <?php
$group_descriptions = [
    'Core Scripts Assets'     => 'Essential JavaScript files required for core WooCommerce functionality.',
    'Mini Cart Assets'        => 'Assets related to the WooCommerce mini cart block and styling. Keep these disabled if you are using a mini cart feature on all pages.',
    'Block Styles Assets'     => 'Styles used for WooCommerce Gutenberg blocks and components.',
    'General Styles Assets'   => 'Additional layout and theme styles that WooCommerce loads sitewide.',
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
<p style="margin-top: -10px; font-size: 14px; color: #555; margin-bottom: 10px;">
    <?php echo esc_html($desc); ?><?php echo $highlight; ?>
</p>
    <?php foreach ($group_assets as $handle => $asset):
        $label   = $asset['label'];
        $desc    = $asset['desc'];
        $checked = !isset($options[$handle]) || $options[$handle] === '1';
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
            </div>
        <div style="flex: 1; border-left: 1px solid #ccc; padding-left: 20px;max-width: 444px;">
            <style>.settings-sidebar {background: #444 url('<?php echo plugins_url('assets/images/box-bg.png', __FILE__); ?>') no-repeat center center;}</style>
<div class="settings-sidebar">
    <a href="https://www.wpfixit.com" target="_blank" class="wpfi-hover-raise">
        <img src="<?php echo plugins_url('assets/images/wpfi-logo.webp', __FILE__); ?>" alt="WP Fix It" style="max-width: 150px; height: auto;" loading="lazy" decoding="async">
    </a>
    <p>This plugin is brought to you by WP Fix It.<br>Experts in instant WordPress support!</p>
    <a href="https://www.wpfixit.com/save/20-off/?ref=1" target="_blank" class="wpfi-hover-raise">
        <img src="<?php echo plugins_url('assets/images/save.png', __FILE__); ?>" alt="WP Fix It - WordPress Experts" style="border-radius: 12px; width: 325px;" title="20% Off WP Fix It's Services" loading="lazy" decoding="async">
    </a>
</div>
<div class="settings-sidebar">
    <h2 style="margin-top:15px; font-size:25px; font-weight:700; color:#d16aff;">Speed Testing Tool</h2>
    <p>Instantly check your website&#8217;s speed and performance, identifying areas for improvement to ensure fast loading times and a seamless experience for all users.</p>
    <a href="https://www.wpfixit.com/tools/speed-check/" target="_blank" class="wpfi-hover-raise">
        <img src="<?php echo plugins_url('assets/images/speed-check.png', __FILE__); ?>" alt="WP Fix It - WordPress Experts" style="border-radius: 12px; width: 325px;" title="Speed Testing Tool" loading="lazy" decoding="async">
    </a>
</div>
<div class="settings-sidebar">
    <h2 style="margin-top:15px; font-size:25px; font-weight:700; color:#d16aff;">WordPress Inspector Tool</h2>
    <p>Quickly analyze any WordPress site revealing details about its themes, plugins, security, speed and core installation.</p>
    <a href="https://www.wpfixit.com/tools/wp-inspector/" target="_blank" class="wpfi-hover-raise">
        <img src="<?php echo plugins_url('assets/images/wp-inspector.png', __FILE__); ?>" alt="WP Fix It - WordPress Experts" style="border-radius: 12px; width: 325px;" title="WordPress Inspector Tool" loading="lazy" decoding="async">
    </a>
</div>
<div class="settings-sidebar">
    <h2 style="margin-top:15px; font-size:25px; font-weight:700; color:#d16aff;">Device View Tool</h2>
    <p>Easily preview how a website looks on multiple devices, ensuring responsiveness and consistency across different screen sizes.</p>
    <a href="https://www.wpfixit.com/tools/device-view/" target="_blank" class="wpfi-hover-raise">
        <img src="<?php echo plugins_url('assets/images/device-view.png', __FILE__); ?>" alt="WP Fix It - WordPress Experts" style="border-radius: 12px; width: 325px;" title="Device View Tool" loading="lazy" decoding="async">
    </a>
</div>
        </div>
    </div>
    <?php
}
// Dequeue selected WooCommerce assets based on toggles
add_action('wp_enqueue_scripts', 'repair_woocommerce_speed_dequeue_assets', PHP_INT_MAX);
function repair_woocommerce_speed_dequeue_assets() {
    if (!function_exists('is_woocommerce')) return;
    if (is_woocommerce() || is_cart() || is_checkout()) return;
    $options = get_option('repair_woocommerce_speed_options', []);
    $script_handles = [
        'wc-mini-cart-block-frontend',
        'wc-add-to-cart',
        'woocommerce',
        'wc-order-attribution',
    ];
    $style_handles = [
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
// Display list of scripts and styles that are loading
add_action('admin_bar_menu', 'debug_woocommerce_assets_admin_bar', 100);
function debug_woocommerce_assets_admin_bar($wp_admin_bar) {
    if (!is_user_logged_in() || !current_user_can('manage_options') || is_admin()) {
        return;
    }
    global $wp_scripts, $wp_styles;
    $script_list = [];
    $style_list  = [];
    foreach ($wp_scripts->queue as $handle) {
        if (stripos($handle, 'wc-') !== false || stripos($handle, 'woocommerce') !== false || stripos($handle, 'sourcebuster') !== false) {
            $script_list[] = $handle;
        }
    }
    foreach ($wp_styles->queue as $handle) {
        if (stripos($handle, 'wc-') !== false || stripos($handle, 'woocommerce') !== false) {
            $style_list[] = $handle;
        }
    }
    $wp_admin_bar->add_node([
        'id'    => 'woo-assets-debug',
        'title' => '<img src="' . esc_url( plugins_url('assets/images/woo.png', __FILE__) ) . '" style="height:16px;vertical-align:middle;margin-right:6px;"> <span class="ab-label">WooCommerce Assets</span>',
        'href'  => false,
    ]);
    $wp_admin_bar->add_node([
        'id'     => 'woo-assets-scripts',
        'parent' => 'woo-assets-debug',
        'title'  => 'Scripts Loading - ' . count($script_list) . '',
    ]);
    foreach ($script_list as $handle) {
        $wp_admin_bar->add_node([
            'id'     => 'woo-script-' . sanitize_html_class($handle),
            'parent' => 'woo-assets-scripts',
            'title'  => esc_html($handle),
        ]);
    }
    $wp_admin_bar->add_node([
        'id'     => 'woo-assets-styles',
        'parent' => 'woo-assets-debug',
        'title'  => 'Styles Loading - ' . count($style_list) . '',
    ]);
    foreach ($style_list as $handle) {
        $wp_admin_bar->add_node([
            'id'     => 'woo-style-' . sanitize_html_class($handle),
            'parent' => 'woo-assets-styles',
            'title'  => esc_html($handle),
        ]);
    }
    
    $wp_admin_bar->add_node([
        'id'     => 'woo-assets-settings',
        'parent' => 'woo-assets-debug',
        'title'  => 'Enable or Disable Assets',
        'href'   => admin_url('admin.php?page=repair_woocommerce_speed_settings'),
    ]);
}