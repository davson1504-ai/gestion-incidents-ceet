const bootDashboardCharts = () => {
    const ChartLibrary = window.Chart;
    const payload = window.dashboardChartData;

    if (!ChartLibrary || !payload) {
        return;
    }

    const axisColor = '#6b7280';
    const gridColor = 'rgba(107, 114, 128, 0.12)';
    const rankColors = ['#991b1b', '#b91c1c', '#dc2626', '#ef4444', '#f87171', '#fca5a5', '#fecaca'];

    const centerTextPlugin = {
        id: 'ceetDonutCenterText',
        afterDraw(chart, _args, options) {
            if (chart.config.type !== 'doughnut' || !options?.text) {
                return;
            }

            const { ctx } = chart;
            const meta = chart.getDatasetMeta(0);

            if (!meta?.data?.length) {
                return;
            }

            const { x, y } = meta.data[0];

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#111827';
            ctx.font = '700 28px Figtree, sans-serif';
            ctx.fillText(String(options.text), x, y - 8);
            ctx.fillStyle = axisColor;
            ctx.font = '500 12px Figtree, sans-serif';
            ctx.fillText('Total', x, y + 18);
            ctx.restore();
        },
    };

    ChartLibrary.register(centerTextPlugin);

    const buildDonut = (elementId, source) => {
        const canvas = document.getElementById(elementId);

        if (!canvas) {
            return;
        }

        const totals = source.map((item) => item.total);

        new ChartLibrary(canvas, {
            type: 'doughnut',
            data: {
                labels: source.map((item) => item.label),
                datasets: [
                    {
                        data: totals,
                        backgroundColor: source.map((item) => item.color),
                        borderWidth: 0,
                        hoverOffset: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: axisColor,
                            boxWidth: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.label}: ${context.formattedValue}`,
                        },
                    },
                    ceetDonutCenterText: {
                        text: totals.reduce((sum, value) => sum + Number(value || 0), 0),
                    },
                },
            },
        });
    };

    const timeseriesCanvas = document.getElementById('dashboard-timeseries-chart');
    if (timeseriesCanvas) {
        new ChartLibrary(timeseriesCanvas, {
            type: 'line',
            data: {
                labels: payload.timeseries.labels,
                datasets: [
                    {
                        label: 'Incidents',
                        data: payload.timeseries.data,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.12)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
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
                        ticks: {
                            color: axisColor,
                            precision: 0,
                        },
                        grid: {
                            color: gridColor,
                            drawBorder: false,
                        },
                    },
                },
            },
        });
    }

    const topDepartCanvas = document.getElementById('dashboard-top-depart-chart');
    if (topDepartCanvas) {
        new ChartLibrary(topDepartCanvas, {
            type: 'bar',
            data: {
                labels: payload.topDepart.map((item) => item.label),
                datasets: [
                    {
                        data: payload.topDepart.map((item) => item.total),
                        backgroundColor: rankColors.slice(0, payload.topDepart.length),
                        borderRadius: 10,
                        borderSkipped: false,
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            color: axisColor,
                            precision: 0,
                        },
                        grid: {
                            color: gridColor,
                            drawBorder: false,
                        },
                    },
                    y: {
                        ticks: { color: axisColor },
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                    },
                },
            },
        });
    }

    buildDonut('dashboard-status-chart', payload.byStatus);
    buildDonut('dashboard-priority-chart', payload.byPriorite);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootDashboardCharts);
} else {
    bootDashboardCharts();
}
