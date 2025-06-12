<?php
// Register the meta box
add_action('add_meta_boxes', function () {
    add_meta_box(
        'repair_woo_per_page_handles',
        'Speed Repair',
        'repair_woo_render_page_handles_metabox',
        ['page', 'post'],
        'side',
        'high'
    );
});

function repair_woo_render_page_handles_metabox($post) {
    $value = get_post_meta($post->ID, '_repair_woo_custom_handles', true);
    wp_nonce_field('repair_woo_custom_handles_nonce', 'repair_woo_custom_handles_field');

    $image_url = plugins_url('../assets/images/woo.png', __FILE__); // adjust path as needed

    echo '<div style="display: flex; flex-direction: column; gap: 10px;">';

    // Add image at the top
    echo '<div style="text-align: center; margin-bottom: 10px;">
        <img src="' . esc_url($image_url) . '" alt="Woo Speed Repair" style="width: 60px; height: auto;" />
    </div>';

    // Label + textarea
    echo '<label for="repair_woo_custom_handles" style="font-weight: 600; font-size: 14px; color: #333;">Enter WooCommerce Handles</label>';

    echo '<textarea 
        name="repair_woo_custom_handles" 
        id="repair_woo_custom_handles"
        rows="6"
        style="width:100%; padding: 10px; font-size: 13px; border: 1px solid #ccc; border-radius: 6px; resize: vertical; background: #fff;"
        placeholder="e.g. wc-add-to-cart&#10;woocommerce-inline">' . esc_textarea($value) . '</textarea>';

    echo '<p style="font-size:12px; color: #666; margin: 0;">Enter one script or style <strong>handle per line</strong> to dequeue on this page only.</p>';

    echo '</div>';
}

// Save the per-page meta
add_action('save_post', function ($post_id) {
    if (!isset($_POST['repair_woo_custom_handles_field']) || 
        !wp_verify_nonce($_POST['repair_woo_custom_handles_field'], 'repair_woo_custom_handles_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $handles = sanitize_textarea_field($_POST['repair_woo_custom_handles'] ?? '');
    update_post_meta($post_id, '_repair_woo_custom_handles', $handles);
});

// Dequeue logic applied globally + per-page
add_action('wp_enqueue_scripts', function () {
    if (is_admin()) return;

    $global_raw = get_option('repair_woocommerce_speed_custom_handles', '');
    $global = array_filter(array_map('trim', explode("\n", $global_raw)));

    $post_id = get_queried_object_id();
    $page_raw = get_post_meta($post_id, '_repair_woo_custom_handles', true);
    $page = array_filter(array_map('trim', explode("\n", $page_raw)));

    $handles = array_unique(array_merge($global, $page));

    foreach ($handles as $handle) {
    wp_dequeue_style($handle);
    wp_deregister_style($handle);
    wp_dequeue_script($handle);
    wp_deregister_script($handle);
}
}, 100);

add_action('wp_ajax_wsr_save_handles', function () {
    check_ajax_referer('wsr_nonce', '_ajax_nonce');

    $post_id = intval($_POST['post_id'] ?? 0);
    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_send_json_error('Permission denied');
    }

    $scripts = json_decode(stripslashes($_POST['scripts'] ?? '[]'), true);
    $styles  = json_decode(stripslashes($_POST['styles'] ?? '[]'), true);

    $handles = implode("\n", array_merge($scripts ?: [], $styles ?: []));
    update_post_meta($post_id, '_repair_woo_custom_handles', sanitize_textarea_field($handles));

    wp_send_json_success(['saved' => true]);
});
add_action('wp_ajax_wsr_clear_handles', function () {
    check_ajax_referer('wsr_nonce', '_ajax_nonce');

    $post_id = intval($_POST['post_id'] ?? 0);
    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_send_json_error('Permission denied');
    }

    delete_post_meta($post_id, '_repair_woo_custom_handles');

    wp_send_json_success(['cleared' => true]);
});
add_action('wp_ajax_wsr_save_single_handle', function () {
    check_ajax_referer('wsr_nonce', '_ajax_nonce');

    $post_id = intval($_POST['post_id'] ?? 0);
    $handle = sanitize_text_field($_POST['handle'] ?? '');

    if (!$post_id || !$handle || !current_user_can('edit_post', $post_id)) {
        wp_send_json_error('Invalid request');
    }

    $existing = get_post_meta($post_id, '_repair_woo_custom_handles', true);
    $existing_array = array_filter(array_map('trim', explode("\n", $existing)));

    if (!in_array($handle, $existing_array)) {
        $existing_array[] = $handle;
        $updated = implode("\n", $existing_array);
        update_post_meta($post_id, '_repair_woo_custom_handles', $updated);
    }

    wp_send_json_success(['added' => $handle]);
});