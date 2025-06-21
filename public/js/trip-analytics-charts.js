// Trip Trend Chart
const tripTrendCtx = document.getElementById('tripTrendChart').getContext('2d');
new Chart(tripTrendCtx, {
    type: 'line',
    data: {
        labels: window.chartData.tripTrend.months.map(date => new Date(date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' })),
        datasets: [
            {
                label: 'Number of Trips',
                data: window.chartData.tripTrend.trips,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                yAxisID: 'y',
                fill: true
            },
            {
                label: 'Total Distance (km)',
                data: window.chartData.tripTrend.distances,
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                yAxisID: 'y1',
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Number of Trips'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Distance (km)'
                },
                grid: {
                    drawOnChartArea: false
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function (context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.dataset.yAxisID === 'y1') {
                            label += context.parsed.y.toFixed(2) + ' km';
                        } else {
                            label += context.parsed.y;
                        }
                        return label;
                    }
                }
            }
        }
    }
});

// Trips by Vehicle Type Chart
const tripsByTypeCtx = document.getElementById('tripsByTypeChart').getContext('2d');
new Chart(tripsByTypeCtx, {
    type: 'doughnut',
    data: {
        labels: window.chartData.tripsByType.types,
        datasets: [{
            data: window.chartData.tripsByType.distances,
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(139, 92, 246, 0.8)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function (context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value.toFixed(2)} km (${percentage}%)`;
                    }
                }
            }
        }
    }
}); 