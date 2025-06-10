document.addEventListener('DOMContentLoaded', function () {
    const pieCtx = document.getElementById('wooAssetPie')?.getContext('2d');
    const barCtx = document.getElementById('wooAssetBar')?.getContext('2d');

    if (pieCtx && window.wooAssetChartData) {
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Disabled', 'Still Active'],
                datasets: [{
                    data: wooAssetChartData.pie,
                    backgroundColor: ['#00d78b', '#ff6384'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: 'WooCommerce Asset Status' }
                }
            }
        });
    }

    if (barCtx && window.wooAssetChartData) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: wooAssetChartData.bar.labels,
                datasets: [{
                    label: 'Assets Disabled per Group',
                    data: wooAssetChartData.bar.values,
                    backgroundColor: '#d16aff'
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Disabled Assets by Group' }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }
});
