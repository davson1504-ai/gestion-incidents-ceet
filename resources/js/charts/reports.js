const bootReportsCharts = () => {
    const ChartLibrary = window.Chart;
    const payload = window.reportsChartData;

    if (!ChartLibrary || !payload) {
        return;
    }

    const axisColor = '#6b7280';
    const gridColor = 'rgba(107, 114, 128, 0.12)';
    const palette = ['#ef2433', '#facc15', '#dc2626', '#f97316'];

    const evolutionCanvas = document.getElementById('reports-evolution-chart');
    if (evolutionCanvas) {
        new ChartLibrary(evolutionCanvas, {
            type: 'line',
            data: {
                labels: payload.evolutionLabels,
                datasets: [
                    {
                        label: 'Nombre d\'incidents',
                        data: payload.evolutionIncidentData,
                        borderColor: '#ef2433',
                        backgroundColor: 'rgba(239, 36, 51, 0.14)',
                        borderWidth: 3,
                        tension: 0.35,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Durée moyenne (min)',
                        data: payload.evolutionDurationData,
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.18)',
                        borderWidth: 3,
                        tension: 0.35,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: axisColor,
                        },
                    },
                },
                scales: {
                    x: {
                        ticks: { color: axisColor },
                        grid: {
                            color: gridColor,
                            drawBorder: false,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        ticks: {
                            color: axisColor,
                            precision: 0,
                        },
                        grid: {
                            color: gridColor,
                            drawBorder: false,
                        },
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        ticks: {
                            color: axisColor,
                            precision: 0,
                        },
                        grid: {
                            drawOnChartArea: false,
                            drawBorder: false,
                        },
                    },
                },
            },
        });
    }

    const typeCanvas = document.getElementById('reports-type-chart');
    if (typeCanvas) {
        new ChartLibrary(typeCanvas, {
            type: 'doughnut',
            data: {
                labels: payload.byType.map((item) => item.label),
                datasets: [
                    {
                        data: payload.byType.map((item) => item.total),
                        backgroundColor: palette.slice(0, payload.byType.length),
                        borderWidth: 0,
                        hoverOffset: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '64%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: axisColor,
                            boxWidth: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                        },
                    },
                },
            },
        });
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootReportsCharts);
} else {
    bootReportsCharts();
}
