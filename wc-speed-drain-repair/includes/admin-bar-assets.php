<?php

//Admin bar menu entries
add_action('admin_bar_menu', 'wad_add_admin_bar_items', 100);
function wad_add_admin_bar_items($wp_admin_bar) {
    if (is_admin() || !is_user_logged_in() || !current_user_can('manage_options')) {
        return;
    }

    $wp_admin_bar->add_node([
        'id'    => 'woo-assets-debug',
        'title' => '<img src="' . esc_url(plugins_url('../assets/images/woo.png', __FILE__)) . '" style="height:16px;vertical-align:middle;margin-right:6px;" /> <span class="ab-label">WooCommerce Assets</span>',
        'href'  => false,
    ]);

    $wp_admin_bar->add_node([
        'id'     => 'woo-assets-scripts',
        'parent' => 'woo-assets-debug',
        'title'  => 'Scripts Loading â€“ 0',
        'href'   => '#',
        'meta'   => [
            'onclick' => 'event.preventDefault(); document.getElementById("wsr-woo-assets-overlay-scripts").style.display = "block";'
        ],
    ]);
    $wp_admin_bar->add_node([
        'id'     => 'woo-assets-styles',
        'parent' => 'woo-assets-debug',
        'title'  => 'Styles Loading â€“ 0',
        'href'   => '#',
        'meta'   => [
            'onclick' => 'event.preventDefault(); document.getElementById("woo-assets-overlay-styles").style.display = "block";'
        ],
    ]);
    
if (!(is_front_page() && !is_singular())) {

    $wp_admin_bar->add_node([
        'id'     => 'woo-assets-save-meta',
        'parent' => 'woo-assets-debug',
        'title'  => 'Disable All on This Page',
        'href'   => '#',
        'meta'   => [
            'onclick' => 'event.preventDefault(); saveHandlesToMeta();'
        ],
    ]);
}

    $post_id = get_the_ID();

// Only show if there are saved handles in the meta box (e.g. from the edit screen)
$page_raw = get_post_meta(get_the_ID(), '_repair_woo_custom_handles', true);
if (!empty(trim($page_raw))) {
        $wp_admin_bar->add_node([
            'id'     => 'woo-assets-remove-meta',
            'parent' => 'woo-assets-debug',
            'title'  => 'Enable All on This Page',
            'href'   => '#',
            'meta'   => [
                'onclick' => 'event.preventDefault(); removeHandlesFromMeta();'
            ],
        ]);
    }

    $current_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $test_url = admin_url('admin.php?page=repair_woocommerce_speed_settings&speedtest_url=' . urlencode($current_url));


    $wp_admin_bar->add_node([
        'id'     => 'woo-assets-settings',
        'parent' => 'woo-assets-debug',
        'title'  => 'Enable or Disable Globally',
        'href'   => admin_url('admin.php?page=repair_woocommerce_speed_settings'),
    ]);
    
        $wp_admin_bar->add_node([
        'id'     => 'woo-assets-speedtest',
        'parent' => 'woo-assets-debug',
        'title'  => '&#128200; Savings Test This Page',
        'href'   => $test_url,
    ]);

}

// Show Assrys Counts in Admin Menu Bar
add_action('wp_footer', 'woo_assets_loading_on_page', PHP_INT_MAX);
function woo_assets_loading_on_page() {
    if (is_admin() || !is_user_logged_in() || !current_user_can('manage_options')) {
        return;
    }

    global $wp_scripts, $wp_styles;

    $scripts = array_filter((array) $wp_scripts->done, function($h) {
    return (
        (strpos($h, 'wc-') === 0 || str_contains($h, 'woocommerce') || str_contains($h, 'sourcebuster')) &&
        !in_array($h, ['wc-blocks-registry-dependency-error', 'wc-settings-dependency-error'])
    );
});

$styles = array_filter((array) $wp_styles->done, function($h) {
    return (
        (strpos($h, 'wc-') === 0 || str_contains($h, 'woocommerce') || strpos($h, 'brands-') === 0) &&
        !in_array($h, ['wc-blocks-registry-dependency-error', 'wc-settings-dependency-error'])
    );
});
    ?>
<script>
const wsrDisabledHandles = new Set(); 
document.addEventListener('DOMContentLoaded', () => {

    // --- Hide "Disable All" menu item if no handles found ---
    const allHandles = [
        ...document.querySelectorAll('#wsr-woo-assets-modal-scripts .woo-handle'),
        ...document.querySelectorAll('#woo-assets-modal-styles .woo-handle')
    ];
    const handles = allHandles.map(span => span.textContent.trim()).filter(Boolean);
    
    if (handles.length === 0) {
        const menuItem = document.getElementById('wp-admin-bar-woo-assets-save-meta');
        if (menuItem) menuItem.style.display = 'none';
    }

    // --- Update admin bar script/style counts ---
    const scriptsCount = <?= count($scripts) ?>;
    const stylesCount  = <?= count($styles) ?>;

    const scriptNode = document.querySelector('#wp-admin-bar-woo-assets-scripts .ab-item');
    const styleNode  = document.querySelector('#wp-admin-bar-woo-assets-styles .ab-item');

    if (scriptNode) scriptNode.textContent = `Scripts Loading â€“ ${scriptsCount}`;
    if (styleNode)  styleNode.textContent = `Styles Loading â€“ ${stylesCount}`;
});

// --- Dismiss overlays on Escape key ---
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.woo-overlay').forEach(overlay => {
            overlay.style.display = 'none';
        });
    }
});

// --- Dismiss overlays on background click ---
document.addEventListener('click', e => {
    document.querySelectorAll('.woo-overlay').forEach(overlay => {
        if (e.target === overlay) {
            overlay.style.display = 'none';
        }
    });
});

// --- Copy handles from modal ---
function copyHandles(modalId) {
    const modal = document.getElementById(modalId);
    const listItems = modal.querySelectorAll('ul li');

    const handles = Array.from(listItems).map(li => {
        const span = li.querySelector('.woo-handle');
        return span ? span.textContent.trim() : '';
    }).filter(Boolean).join("\n");

    navigator.clipboard.writeText(handles).then(() => {
        const button = modal.querySelector('.woo-copy-btn');
        if (button) {
            const originalText = button.textContent;
            button.textContent = "Handles Copied";
            button.disabled = true;

            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
            }, 2000);
        }
    }).catch(err => {
        alert("Failed to copy handles: " + err);
    });
}

// --- Confirmation box diable assets ---
function showWsrFeedback(message, callback = null) {
    const overlay = document.getElementById('wsr-feedback-dialog');
    const messageBox = document.getElementById('wsr-feedback-message');
    const okButton = document.getElementById('wsr-feedback-ok');

    messageBox.textContent = message;

    // Reset click listeners safely
    const newButton = okButton.cloneNode(true);
    okButton.parentNode.replaceChild(newButton, okButton);

    newButton.addEventListener('click', () => {
        overlay.style.display = 'none';
        if (typeof callback === 'function') callback();
    });

    overlay.style.display = 'block';
}

// --- Save handles to meta via AJAX ---
function saveHandlesToMeta() {
    // Grab all visible handles still in the DOM
    const visibleHandles = [
        ...document.querySelectorAll('#wsr-woo-assets-modal-scripts .woo-handle'),
        ...document.querySelectorAll('#woo-assets-modal-styles .woo-handle')
    ].map(span => span.textContent.trim()).filter(Boolean);

    // Combine with individually disabled handles
    const allHandles = Array.from(new Set([...visibleHandles, ...wsrDisabledHandles]));

    if (allHandles.length === 0) {
        alert("No WooCommerce assets found to disable");
        return;
    }

    fetch(wsr_ajax.url, {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'wsr_save_handles',
            _ajax_nonce: wsr_ajax.nonce,
            post_id: wsr_ajax.postId,
            scripts: JSON.stringify(allHandles),
            styles: JSON.stringify([]) // optional: separate styles if needed
        })
    }).then(res => res.json()).then(response => {
        if (response.success) {
            showWsrFeedback("All WooCommerce Assets Disabled!", () => location.reload());
        } else {
            showWsrFeedback("Failed to disable because this is not a standard page or post template.");
        }
    }).catch(() => {
        alert("AJAX error occurred");
    });
}

// --- Remove handles from meta via AJAX ---
function removeHandlesFromMeta() {
    const handles = [
        ...document.querySelectorAll('#wsr-woo-assets-modal-scripts .woo-handle'),
        ...document.querySelectorAll('#woo-assets-modal-styles .woo-handle')
    ].map(span => span.textContent.trim()).filter(Boolean);

    fetch(wsr_ajax.url, {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'wsr_clear_handles',
            _ajax_nonce: wsr_ajax.nonce,
            post_id: wsr_ajax.postId
        })
    }).then(res => res.json()).then(response => {
if (response.success) {
    showWsrFeedback("All WooCommerce Assets Enabled!", () => location.reload());
} else {
    showWsrFeedback("Failed to enable WooCommerce assets.");
}
    }).catch(() => {
        alert("AJAX error occurred");
    });
}
// --- Disable a specific handle ---
document.addEventListener('click', function (e) {
    if (!e.target.matches('.disable-handle-btn')) return;

    const button = e.target;
    const handle = button.getAttribute('data-handle');
    const listItem = button.closest('li');
    if (!handle || !listItem) return;

    // Collect existing handles from meta + append this one
    fetch(wsr_ajax.url, {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'wsr_save_single_handle',
            _ajax_nonce: wsr_ajax.nonce,
            post_id: wsr_ajax.postId,
            handle: handle
        })
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            wsrDisabledHandles.add(handle); // í ½í±ˆ Track handle
            listItem.remove(); // remove from the DOM
        } else {
            showWsrFeedback("Failed to disable because this is not a standard page or post template.");
        }
    })
    .catch(() => alert("AJAX error occurred while disabling handle."));
});
</script>
    <style>
    .woo-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 999998;
    }
    .woo-modal {
        position: relative;
        top: 10%;
        left: 50%;
        transform: translateX(-50%);
        width: 45%;
        max-width: 700px;
        background: #222;
        color: #efefef;
        border: 5px solid #d16aff;
        z-index: 999999;
        padding: 20px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.4);
        border-radius: 12px;
        font-family: system-ui;
    }
    .woo-modal h2 {
        margin-top: 0;
    }
    .woo-modal button.close {
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 18px;
        background: #d16aff;
        border: none;
        color: #fff;
        cursor: pointer;
        border-radius: 300px;
    }

    .woo-modal ul {
        max-height: 200px;
        overflow-y: auto;
        padding-left: 20px;
        margin: 0;
    }

    .woo-modal li {
        margin-bottom: 4px;
        list-style: none;
    }

    .woo-badge {
        display: inline-flex;
        width: 33px;
        height: 33px;
        font-size: 25px;
        font-weight: bold;
        align-items: center;
        justify-content: center;
        background-color: #FFFF97;
        color: #000;
        border-radius: 50%;
        margin-left: 8px;
        vertical-align: middle;
        padding:10px;
    }

    .woo-copy-btn {
        margin-bottom: 15px;
        padding: 6px 12px;
        font-weight: bold;
        background: #d16aff;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .woo-modal ul li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    font-size: 14px;
}
.woo-modal ul li .woo-handle {
    font-weight: 500;
    color: #fff;
    font-size: 20px;
    text-align: left;
    flex: 1; 
}
    .woo-overlay-confirmation {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 99999999;
    }
    .woo-modal-confirmation {
        position: relative;
        top: 23%;
        left: 50%;
        transform: translateX(-50%);
        width: 25%;
        max-width: 500px;
        background: #222;
        color: #efefef;
        border: 5px solid #d16aff;
        z-index: 999999;
        padding: 20px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.4);
        border-radius: 12px;
        font-family: system-ui;
    }
    .woo-copy-btn-confirmation {
    margin-bottom: 15px;
    padding: 10px 12px;
    font-weight: bold;
    background: #d16aff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
}
.disable-handle-btn {
    margin-right: 5%;
    padding: 3px 6px;
    background: #ff4c4c;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    flex-shrink: 0; /* Prevent button from shrinking */
}
.disable-handle-btn:hover {
background: #00D78B;
}

    </style>
<?php
function format_plugin_name($slug) {
    // Replace hyphens and underscores with spaces
    $slug = str_replace(['-', '_'], ' ', strtolower(trim($slug)));

    // Capitalize words
    $slug = ucwords($slug);

    // Replace any variation of 'woocommerce' with 'WooCommerce'
    return str_ireplace('Woocommerce', 'WooCommerce', $slug);
}
?>
<div id="wsr-feedback-dialog" class="woo-overlay-confirmation" style="display: none;">
    <div class="woo-modal-confirmation" style="text-align: center;">
        <h2 id="wsr-feedback-message" style="font-size: 23px; color: #fff;"></h2>
        <button id="wsr-feedback-ok" class="woo-copy-btn-confirmation" style="margin-top: 20px;">Close This</button>
    </div>
</div>


    <!-- Scripts Modal -->
    <div id="wsr-woo-assets-overlay-scripts" class="woo-overlay">
        <div id="wsr-woo-assets-modal-scripts" class="woo-modal">
            <button class="close" onclick="document.getElementById('wsr-woo-assets-overlay-scripts').style.display='none'">âœ–</button>
            <h2>WooCommerce Scripts <span class="woo-badge"><?= count($scripts) ?></span></h2>
            <p style="font-size: 18px;color: #efefef;margin-top: -16px;">These handles are actively loading on this page.</p>
            
                <button class="woo-copy-btn" onclick="copyHandles('wsr-woo-assets-modal-scripts')">Copy Handles</button>
                <div style="display: flex; justify-content: space-between; font-size: 13px; color: #aaa; padding: 5px 15px; border-bottom: 1px solid #555; margin-bottom: 8px;">
            <strong>Action and Handle Name</strong>
            <strong>Plugin Loading It</strong>
        </div>
            <ul>
    <?php foreach ($scripts as $handle): ?>
        <?php
        $src = '';
        if (isset($wp_scripts->registered[$handle])) {
            $src = $wp_scripts->registered[$handle]->src;
        } elseif (isset($wp_styles->registered[$handle])) {
            $src = $wp_styles->registered[$handle]->src;
        }

        $plugin_or_theme = '';
        if ($src) {
            $relative_path = str_replace(home_url(), '', $src);
            if (strpos($relative_path, '/wp-content/plugins/') !== false) {
                preg_match('#/wp-content/plugins/([^/]+)/#', $relative_path, $matches);
                $plugin_or_theme = !empty($matches[1]) ? $matches[1] : 'WooCommerce';
            } elseif (strpos($relative_path, '/wp-content/themes/') !== false) {
                preg_match('#/wp-content/themes/([^/]+)/#', $relative_path, $matches);
                $plugin_or_theme = !empty($matches[1]) ? $matches[1] : 'WooCommerce';
            }
        }
        ?>
        <li style="display: flex; justify-content: space-between; align-items: center; padding-right: 15px;">
            <button class="disable-handle-btn" data-handle="<?= esc_attr($handle) ?>">Disable</button>
<span class="woo-handle"><?= esc_html($handle) ?></span>
            <span style="color: #FFFF97; font-size: 14px; text-transform: capitalize;">
                <?= esc_html($plugin_or_theme ? format_plugin_name($plugin_or_theme) : 'WooCommerce') ?>
            </span>

        </li>
    <?php endforeach; ?>
</ul>

        </div>
    </div>

<!-- Styles Modal -->
<div id="woo-assets-overlay-styles" class="woo-overlay">
    <div id="woo-assets-modal-styles" class="woo-modal">
        <button class="close" onclick="document.getElementById('woo-assets-overlay-styles').style.display='none'">âœ–</button>
        <h2>WooCommerce Styles <span class="woo-badge"><?= count($styles) ?></span></h2>
        <p style="font-size: 18px;color: #efefef;margin-top: -16px;">These handles are actively loading on this page.</p>
        
        <button class="woo-copy-btn" onclick="copyHandles('woo-assets-modal-styles')">Copy Handles</button>

        <!-- Column headers -->
        <div style="display: flex; justify-content: space-between; font-size: 13px; color: #aaa; padding: 5px 15px; border-bottom: 1px solid #555; margin-bottom: 8px;">
            <strong>Action and Handle Name</strong>
            <strong>Plugin Loading It</strong>
        </div>

        <ul>
    <?php foreach ($styles as $handle): ?>
        <?php
        $src = '';
        if (isset($wp_styles->registered[$handle])) {
            $src = $wp_styles->registered[$handle]->src;
        } elseif (isset($wp_scripts->registered[$handle])) {
            $src = $wp_scripts->registered[$handle]->src;
        }

        $plugin_or_theme = '';
        if ($src) {
            $relative_path = str_replace(home_url(), '', $src);

            if (strpos($relative_path, '/wp-content/plugins/') !== false) {
                preg_match('#/wp-content/plugins/([^/]+)/#', $relative_path, $matches);
                $plugin_or_theme = !empty($matches[1]) ? $matches[1] : 'WooCommerce';
            } elseif (strpos($relative_path, '/wp-content/themes/') !== false) {
                preg_match('#/wp-content/themes/([^/]+)/#', $relative_path, $matches);
                $plugin_or_theme = !empty($matches[1]) ? $matches[1] : 'WooCommerce';
            }
        }
        ?>
        <li style="display: flex; justify-content: space-between; align-items: center; padding-right: 15px;">
            <button class="disable-handle-btn" data-handle="<?= esc_attr($handle) ?>">Disable</button>
<span class="woo-handle"><?= esc_html($handle) ?></span>
            <span style="color: #FFFF97; font-size: 14px; text-transform: capitalize;">
                <?= esc_html($plugin_or_theme ? format_plugin_name($plugin_or_theme) : 'WooCommerce') ?>
            </span>

        </li>
    <?php endforeach; ?>
</ul>

    </div>
</div>
    <?php
}
add_action('wp_enqueue_scripts', 'wsr_enqueue_frontend_script');
function wsr_enqueue_frontend_script() {
    if (!is_user_logged_in() || !current_user_can('manage_options') || is_admin()) return;

    wp_enqueue_script(
        'wsr-frontend-js',
        plugins_url('../assets/js/frontend.js', __FILE__),
        ['jquery'],
        '1.0',
        true
    );

    wp_localize_script('wsr-frontend-js', 'wsr_ajax', [
        'url'    => admin_url('admin-ajax.php'),
        'nonce'  => wp_create_nonce('wsr_nonce'),
        'postId' => get_queried_object_id(),
    ]);
}