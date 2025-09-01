import Swal from 'sweetalert2'
import ApexCharts from 'apexcharts';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable'; // <-- Impor fungsinya secara langsung

window.jsPDF = jsPDF;
window.autoTable = autoTable;

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

window.ApexCharts = ApexCharts;

window.Swal = Swal

function getGradientColor(value, min = 0, max = 100) {
    const percentage = Math.max(0, Math.min(1, (value - min) / (max - min)));
    const startColor = { r: 239, g: 68, b: 68 };
    const midColor = { r: 251, g: 191, b: 36 };
    const endColor = { r: 34, g: 197, b: 94 };
    let r, g, b;
    if (percentage < 0.5) {
        const p = percentage * 2;
        r = startColor.r + p * (midColor.r - startColor.r);
        g = startColor.g + p * (midColor.g - startColor.g);
        b = startColor.b + p * (midColor.b - startColor.b);
    } else {
        const p = (percentage - 0.5) * 2;
        r = midColor.r + p * (endColor.r - midColor.r);
        g = midColor.g + p * (endColor.g - midColor.g);
        b = midColor.b + p * (endColor.b - midColor.b);
    }
    return `rgb(${Math.round(r)}, ${Math.round(g)}, ${Math.round(b)})`;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('gaugeChart', () => ({
        chart: null,
        initChart(data) {
            const isDarkMode = document.documentElement.classList.contains('dark');
            const options = {
                chart: { type: 'radialBar', height: 300 },
                series: data.series,
                plotOptions: {
                    radialBar: {
                        hollow: { size: '70%' },
                        dataLabels: {
                            name: {
                                show: true,
                                fontSize: '16px',
                                color: isDarkMode ? '#e5e7eb' : '#4b5563',
                                offsetY: -10
                            },
                            value: {
                                show: true,
                                fontSize: '24px',
                                fontWeight: 'bold',
                                offsetY: 10,
                                color: isDarkMode ? '#e5e7eb' : '#1f2937'
                            },
                        },
                    },
                },
                colors: ['#22c55e'],
                labels: ['IKM Instansi'],
            }
            this.chart = new ApexCharts(this.$refs.gauge, options);
            this.chart.render();
        }
    }));

    Alpine.data('barChart', () => ({
        chart: null,
        initChart(data) {
            const isDarkMode = document.documentElement.classList.contains('dark');
            const scores = data.series[0].data;
            const barColors = scores.map(score => getGradientColor(score));

            const options = {
                chart: { type: 'bar', height: 350 },
                series: data.series,
                xaxis: {
                    categories: data.categories,
                    labels: {
                        style: { colors: isDarkMode ? '#e5e7eb' : '#4b5563' }
                    }
                },
                yaxis: {
                    labels: {
                        style: { colors: isDarkMode ? '#e5e7eb' : '#4b5563' }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        distributed: true,
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetX: 10,
                    style: {
                        fontSize: '12px',
                        fontWeight: 'bold',
                        colors: ['#1f2937']
                    },
                    formatter: function (val) {
                        return val.toFixed(2);
                    }
                },
                legend: { show: false },
                colors: barColors,
            }
            this.chart = new ApexCharts(this.$refs.bar, options);
            this.chart.render();
        }
    }));

    Alpine.data('tvGaugeChart', () => ({
        chart: null,
        initChart(data) {
            const isDarkMode = document.documentElement.classList.contains('dark');
            const options = {
                chart: { type: 'radialBar', height: '100%' },
                series: data.series,
                plotOptions: { radialBar: { hollow: { size: '65%' }, dataLabels: { name: { show: true, fontSize: '14px', color: isDarkMode ? '#e5e7eb' : '#4b5563', offsetY: -10 }, value: { show: true, fontSize: '22px', fontWeight: 'bold', offsetY: 5, color: isDarkMode ? '#e5e7eb' : '#1f2937' }, }, }, },
                colors: ['#22c55e'],
                labels: ['IKM Instansi'],
            }
            this.chart = new ApexCharts(this.$refs.gauge, options);
            this.chart.render();
        }
    }));

    Alpine.data('tvBarChart', () => ({
        chart: null,
        initChart(data) {
            const isDarkMode = document.documentElement.classList.contains('dark');
            const scores = data.series[0].data;
            const barColors = scores.map(score => getGradientColor(score));
            const options = {
                chart: { type: 'bar', height: '100%' },
                series: data.series,
                xaxis: { categories: data.categories, labels: { style: { colors: isDarkMode ? '#e5e7eb' : '#4b5563' } } },
                yaxis: { labels: { style: { colors: isDarkMode ? '#e5e7eb' : '#4b5563' } } },
                plotOptions: { bar: { horizontal: true, borderRadius: 4, distributed: true, } },
                dataLabels: { enabled: true, offsetX: 10, style: { fontSize: '12px', fontWeight: 'bold', colors: ['#1f2937'] }, formatter: function (val) { return val.toFixed(2); } },
                legend: { show: false },
                colors: barColors,
            }
            this.chart = new ApexCharts(this.$refs.bar, options);
            this.chart.render();
        }
    }));

    Alpine.data('tvAutoScroller', () => ({
        intervalId: null,
        isPaused: false,
        init(container) {
            container.addEventListener('mouseenter', () => this.isPaused = true);
            container.addEventListener('mouseleave', () => this.isPaused = false);
            this.intervalId = setInterval(() => {
                if (this.isPaused || container.scrollHeight <= container.clientHeight) { return; }
                if (container.scrollTop + container.clientHeight >= container.scrollHeight) {
                    this.isPaused = true;
                    setTimeout(() => {
                        container.scrollTo({ top: 0, behavior: 'smooth' });
                        setTimeout(() => { this.isPaused = false; }, 1000);
                    }, 3000);
                } else {
                    container.scrollTop += 1;
                }
            }, 50);
        },
        destroy() {
            if (this.intervalId) { clearInterval(this.intervalId); }
        }
    }));
});
