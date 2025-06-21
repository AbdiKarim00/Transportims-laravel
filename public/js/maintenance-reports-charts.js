// Maintenance Trend Chart
const maintenanceTrendCtx = document.getElementById('maintenanceTrendChart').getContext('2d');
new Chart(maintenanceTrendCtx, {
    type: 'line',
    data: {
        labels: window.chartData.maintenanceTrend.months,
        datasets: [
            {
                label: 'Maintenance Count',
                data: window.chartData.maintenanceTrend.counts,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                yAxisID: 'y'
            },
            {
                label: 'Cost (KSh)',
                data: window.chartData.maintenanceTrend.costs,
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        stacked: false,
        plugins: {
            title: {
                display: true,
                text: 'Maintenance Trend Over Time'
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.dataset.yAxisID === 'y1') {
                            label += 'KSh ' + context.parsed.y.toLocaleString();
                        } else {
                            label += context.parsed.y;
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Number of Services'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Cost (KSh)'
                },
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});

// Maintenance by Vehicle Type Chart
const maintenanceByTypeCtx = document.getElementById('maintenanceByTypeChart').getContext('2d');
new Chart(maintenanceByTypeCtx, {
    type: 'doughnut',
    data: {
        labels: window.chartData.maintenanceByType.types,
        datasets: [{
            data: window.chartData.maintenanceByType.costs,
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(139, 92, 246, 0.8)'
            ],
            borderColor: [
                'rgb(59, 130, 246)',
                'rgb(16, 185, 129)',
                'rgb(245, 158, 11)',
                'rgb(239, 68, 68)',
                'rgb(139, 92, 246)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Maintenance Cost by Vehicle Type'
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
                        return `${label}: KSh ${value.toLocaleString()} (${percentage}%)`;
                    }
                }
            }
        }
    }
}); 