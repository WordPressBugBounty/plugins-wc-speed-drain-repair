document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const passedUrl = urlParams.get('speedtest_url');
    if (passedUrl) {
        const input = document.getElementById('nw_test_url');
        if (input) input.value = passedUrl;

        // Run the test automatically after short delay
        setTimeout(() => {
            if (typeof runSpeedTest === 'function') {
                runSpeedTest(passedUrl);
            } else {
                console.warn('runSpeedTest() is not defined.');
            }
        }, 400);
    }
});