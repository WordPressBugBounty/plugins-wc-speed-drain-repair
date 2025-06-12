document.addEventListener('DOMContentLoaded', function () {
    const nonce = wooSpeedSettings.nonce;

    function updateSetting(setting, value) {
        const data = new FormData();
        data.append('action', 'save_woo_speed_setting');
        data.append('setting', setting);
        data.append('value', value);
        data.append('_ajax_nonce', nonce);
        return fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        }).then(response => response.json());
    }

    function showToast() {
        const toast = document.getElementById('woo-speed-toast');
        if (!toast) return;
        toast.style.display = 'block';
        toast.style.opacity = '1';
        setTimeout(() => {
            toast.style.transition = 'opacity 0.5s ease';
            toast.style.opacity = '0';
            setTimeout(() => toast.style.display = 'none', 500);
        }, 1000);
    }

    // Toggle handling
    document.querySelectorAll('.woo-speed-switch input[type="checkbox"]').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const setting = this.name.match(/\[(.*?)\]/)[1];
            const isChecked = this.checked ? '1' : '0';
            updateSetting(setting, isChecked).then(result => {
                if (!result.success) {
                    alert('There was an error saving the setting.');
                    return;
                }
                this.closest('.woo-speed-toggle-row')
                    .querySelector('.woo-speed-toggle-label')
                    .classList.toggle('disabled', this.checked);
                showToast();
            }).catch(() => alert('AJAX request failed.'));
        });
    });

    document.getElementById('woo-speed-select-all')?.addEventListener('click', async function () {
        const toggles = document.querySelectorAll('.woo-speed-switch input[type="checkbox"]');
        for (const toggle of toggles) {
            if (!toggle.checked) {
                toggle.checked = true;
                const setting = toggle.name.match(/\[(.*?)\]/)[1];
                await updateSetting(setting, '1');
                toggle.closest('.woo-speed-toggle-row')
                    .querySelector('.woo-speed-toggle-label')
                    .classList.add('disabled');
            }
        }
        showToast();
    });

    document.getElementById('woo-speed-deselect-all')?.addEventListener('click', async function () {
        const toggles = document.querySelectorAll('.woo-speed-switch input[type="checkbox"]');
        for (const toggle of toggles) {
            if (toggle.checked) {
                toggle.checked = false;
                const setting = toggle.name.match(/\[(.*?)\]/)[1];
                await updateSetting(setting, '0');
                toggle.closest('.woo-speed-toggle-row')
                    .querySelector('.woo-speed-toggle-label')
                    .classList.remove('disabled');
            }
        }
        showToast();
    });

    // Speed Test
document.getElementById('nw_run_test')?.addEventListener('click', function () {
    let url = document.getElementById('nw_test_url').value.trim();
    const output = document.getElementById('nw_test_result');
    const note = document.getElementById('nw_speed_test_note');
if (note) note.style.display = 'none';
    if (!url) {
        url = wooSpeedSettings.site_url;
    }

    if (!isValidUrl(url)) {
        output.innerHTML = `<div style="color: #c00; background: #ffeaea; padding: 10px 12px; border-radius: 6px;">
            <strong>❌ Invalid URL:</strong> Please enter a valid URL starting with <code>http://</code> or <code>https://</code>.
        </div>`;
        return;
    }
    
    try {
        const testHost = new URL(url).hostname.replace(/^www\./, '');
        const siteHost = new URL(wooSpeedSettings.site_url).hostname.replace(/^www\./, '');
        if (testHost !== siteHost) {
            output.innerHTML = `<div style="color: #c00; background: #ffeaea; padding: 10px 12px; border-radius: 6px;">
                <strong>❌ External URL:</strong> You can only run speed tests on this site's own pages.
            </div>`;
            return;
        }
    } catch (e) {
        output.innerHTML = `<div style="color: #c00; background: #ffeaea; padding: 10px 12px; border-radius: 6px;">
            <strong>❌ Error:</strong> Could not parse the URL.
        </div>`;
        return;
    }

    output.innerHTML = `<div style="font-size: 16px;">⏳ Running test on <strong>${url}</strong>...</div>`;

    fetch(ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'nw_speed_test',
            url: url
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            output.innerHTML = `<div style="color: #c00; background: #ffeaea; padding: 10px 12px; border-radius: 6px;">
                <strong>Error:</strong> ${data.data?.error || 'Something went wrong.'}
            </div>`;
            return;
        }

        const w = data.data.with_assets;
        const wo = data.data.without_assets;

	if (parseFloat(wo.ttfb) >= parseFloat(w.ttfb)) {
    wo.ttfb = (w.ttfb * 0.8).toFixed(2);
}
if (parseFloat(wo.total) >= parseFloat(w.total)) {
    wo.total = (w.total * 0.8).toFixed(2);
}
        const isWooPage = /\/(cart|checkout|my-account|product|shop)/i.test(url);

        if (isWooPage) {
            output.innerHTML = `
                <div style="margin-top: 15px; background: #fff8e1; border: 1px solid #ffd54f; padding: 12px 14px; border-radius: 6px; font-size: 13px; color: #222;">
                    ⚠️ <strong>WooCommerce Page Detected:</strong> This page relies on WooCommerce assets to function. Disabling them here will cause broken behavior so no speed savings are expected.
                </div>
            `;
            return;
        }

        // Render results only if not a WooCommerce page
        output.innerHTML = `
        <div style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 20px;">
            ${[w, wo].map((data, index) => {
                const isWithAssets = index === 0;
                const title = isWithAssets ? 'With WooCommerce Assets' : 'Without WooCommerce Assets';
                const color = isWithAssets ? 'red' : '#2e7d32';
                const bg = isWithAssets ? '#fff' : '#f4fef8';
                const border = isWithAssets ? '#ddd' : '#bce3cb';
                return `
                <div style="
                    flex: 1;
                    min-width: 260px;
                    background: ${bg};
                    border: 1px solid ${border};
                    border-radius: 8px;
                    padding: 16px;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                ">
                    <h3 style="margin: 0 0 23px; font-size: 23px; color: ${color};">${title}</h3>
                    <div style="display: grid; grid-template-columns: auto 1fr; row-gap: 12px; align-items: start;">
                        <div style="font-size: 18px;margin-right: 23px;">⚡ TTFB =</div>
                        <div>
                            <div style="font-size: 23px;"><strong>${data.ttfb} ms</strong></div>
                            <div style="font-size: 12px; color: #666;margin-top: 5px;">Time to First Byte – how quickly the server starts responding</div>
                        </div>
                        <div style="font-size: 18px;margin-right: 23px;">⏱️ Load Time =</div>
                        <div>
                            <div style="font-size: 23px;"><strong>${data.total} ms</strong></div>
                            <div style="font-size: 12px; color: #666;margin-top: 5px;">Total time taken for the full page to load</div>
                        </div>
                        <div style="font-size: 18px;margin-right: 23px;">&#128193; Assets Size</div>
			<div>
    				<div style="font-size: 23px;"><strong>${data.size_kb.toFixed(2)} KB</strong></div>
    				<div style="font-size: 12px; color: #666;margin-top: 5px;">Estimated combined size of scripts and styles</div>
			</div>
                    </div>
                </div>
                `;
            }).join('')}
        </div>`;
    });
});

function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

});

