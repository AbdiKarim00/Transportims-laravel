// Chart configuration
const chartConfig = {
    colors: {
        primary: '#C5A830',
        secondary: '#4B5563',
        tertiary: '#9CA3AF',
        quaternary: '#D1D5DB'
    }
};

// Initialize Consumption Trend Chart
const initConsumptionTrendChart = () => {
    const ctx = document.getElementById('consumptionTrendChart').getContext('2d');
    const data = {
        labels: window.chartData.consumptionTrend.dates,
        datasets: [{
            label: 'Fuel Consumption (L)',
            data: window.chartData.consumptionTrend.liters,
            fill: false,
            borderColor: chartConfig.colors.primary,
            tension: 0.1
        }]
    };

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: '#1f2937'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#6b7280',
                    callback: (value) => `${value} L`
                },
                grid: {
                    color: '#e5e7eb'
                }
            },
            x: {
                ticks: {
                    color: '#6b7280'
                },
                grid: {
                    color: '#e5e7eb'
                }
            }
        }
    };

    new Chart(ctx, {
        type: 'line',
        data,
        options
    });
};

// Initialize Consumption by Type Chart
const initConsumptionByTypeChart = () => {
    const ctx = document.getElementById('consumptionByTypeChart').getContext('2d');
    const data = {
        labels: window.chartData.consumptionByType.types,
        datasets: [{
            data: window.chartData.consumptionByType.liters,
            backgroundColor: Object.values(chartConfig.colors)
        }]
    };

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: '#1f2937'
                }
            }
        }
    };

    new Chart(ctx, {
        type: 'doughnut',
        data,
        options
    });
};

// Initialize all charts when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initConsumptionTrendChart();
    initConsumptionByTypeChart();
}); 