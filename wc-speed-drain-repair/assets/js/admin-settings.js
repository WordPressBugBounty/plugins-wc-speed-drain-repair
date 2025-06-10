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

    // Handle individual toggle changes
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
            }).catch(() => {
                alert('AJAX request failed.');
            });
        });
    });

    // Select All
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

    // Deselect All
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
});