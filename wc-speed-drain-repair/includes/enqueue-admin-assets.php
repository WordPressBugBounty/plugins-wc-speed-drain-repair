<?php
function enqueue_woo_admin_assets($main_plugin_file) {
    add_action('admin_enqueue_scripts', function ($hook) use ($main_plugin_file) {
        if ($hook === 'woocommerce_page_repair_woocommerce_speed_settings') {
            // CSS
            $css_file = plugin_dir_path($main_plugin_file) . 'assets/css/admin-settings.css';
            $css_url  = plugins_url('assets/css/admin-settings.css', $main_plugin_file);
            $css_ver  = file_exists($css_file) ? filemtime($css_file) : time();
            wp_enqueue_style('repair-woo-speed-admin-css', $css_url, [], $css_ver);

            // JS
            $js_file = plugin_dir_path($main_plugin_file) . 'assets/js/admin-settings.js';
            $js_url  = plugins_url('assets/js/admin-settings.js', $main_plugin_file);
            $js_ver  = file_exists($js_file) ? filemtime($js_file) : time();
            wp_enqueue_script('repair-woo-speed-admin-js', $js_url, [], $js_ver, true);

            // Chart.js and plugin chart file
            wp_register_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
            $chart_file = plugin_dir_path($main_plugin_file) . 'assets/js/charts.js';
            $chart_url  = plugins_url('assets/js/charts.js', $main_plugin_file);
            $chart_ver  = file_exists($chart_file) ? filemtime($chart_file) : time();
            wp_enqueue_script('woo-speed-charts-js', $chart_url, ['chartjs'], $chart_ver, true);
            
            $autoload_file = plugin_dir_path($main_plugin_file) . 'assets/js/speedtest-autoload.js';
            $autoload_url  = plugins_url('assets/js/speedtest-autoload.js', $main_plugin_file);
            $autoload_ver  = file_exists($autoload_file) ? filemtime($autoload_file) : time();
            wp_enqueue_script('woo-speed-autoload-js', $autoload_url, ['repair-woo-speed-admin-js'], $autoload_ver, true);


            wp_localize_script('repair-woo-speed-admin-js', 'wooSpeedSettings', [
                'nonce'    => wp_create_nonce('woo_speed_save_nonce'),
                'site_url' => get_site_url(),
            ]);
        }
    });
}