// Impor pustaka inti yang selalu dibutuhkan di setiap halaman
import Swal from 'sweetalert2';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// =======================================================
// FUNGSI-FUNGSI GLOBAL (SEKARANG DENGAN LAZY LOADING)
// =======================================================

window.generateQrCode = function(url) {
    Alpine.nextTick(() => {
        const canvas = document.getElementById('qrcode-canvas');
        const downloadLink = document.getElementById('download-qr');
        if (!canvas || !downloadLink) return;

        // Pustaka diimpor secara dinamis saat fungsi ini dipanggil
        import('qrcode').then(({ default: QRCode }) => {
            const options = { errorCorrectionLevel: 'H', type: 'image/png', quality: 0.92, margin: 1, width: 256 };
            QRCode.toCanvas(canvas, url, options, function (error) {
                if (error) { console.error('Gagal membuat QR Code:', error); return; }
                downloadLink.href = canvas.toDataURL('image/png');
            });
        }).catch(error => console.error('Gagal memuat pustaka QRCode:', error));
    });
}

window.generatePdf = function(data) {
    // Pustaka jsPDF dan autoTable diimpor saat fungsi ini dipanggil
    Promise.all([
        import('jspdf'),
        import('jspdf-autotable')
    ]).then(([{ default: jsPDF }, { default: autoTable }]) => {
        const doc = new jsPDF();

        doc.setFontSize(18);
        doc.text('Laporan Kritik & Saran', 14, 22);
        doc.setFontSize(11);
        doc.setTextColor(100);
        const date = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        doc.text(`Dicetak pada: ${date}`, 14, 29);

        const tableColumn = ["No", "Nama Responden", "Unit Layanan", "Kritik & Saran", "Tanggal"];
        const tableRows = [];
        data.forEach((item, index) => {
            tableRows.push([
                index + 1,
                item.nama,
                item.satker ? item.satker.nama_satker : 'N/A',
                item.kritik_saran,
                new Date(item.created_at).toLocaleDateString('id-ID')
            ]);
        });

        autoTable(doc, {
            head: [tableColumn],
            body: tableRows,
            startY: 35,
            theme: 'grid',
            styles: { fontSize: 8, cellPadding: 2, },
            headStyles: { fillColor: [22, 160, 133], textColor: 255, fontStyle: 'bold', },
            columnStyles: { 0: { cellWidth: 10 }, 1: { cellWidth: 35 }, 2: { cellWidth: 35 }, 3: { cellWidth: 'auto' }, 4: { cellWidth: 20 }, }
        });

        doc.save('laporan-kritik-dan-saran.pdf');
    }).catch(error => console.error('Gagal memuat pustaka PDF:', error));
}


// =======================================================
// KONFIGURASI PUSTAKA INTI
// =======================================================

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
window.Swal = Swal;

// Fungsi ini tetap karena dibutuhkan oleh chart
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

// =======================================================
// DEFINISI KOMPONEN ALPINE.JS (DENGAN LAZY LOADING)
// =======================================================

document.addEventListener('alpine:init', () => {
    // Fungsi initChart sekarang akan me-lazy-load ApexCharts
    const chartInitLogic = (el, data, optionsCallback) => {
        import('apexcharts').then(({ default: ApexCharts }) => {
            const chart = new ApexCharts(el, optionsCallback(data));
            chart.render();
        }).catch(error => console.error('Gagal memuat ApexCharts:', error));
    };

    Alpine.data('gaugeChart', () => ({
        initChart(data) {
            const isDarkMode = document.documentElement.classList.contains('dark');
            chartInitLogic(this.$refs.gauge, data, (chartData) => ({
                chart: { type: 'radialBar', height: 300 },
                series: chartData.series,
                plotOptions: { radialBar: { hollow: { size: '70%' }, dataLabels: { name: { show: true, fontSize: '16px', color: isDarkMode ? '#e5e7eb' : '#4b5563', offsetY: -10 }, value: { show: true, fontSize: '24px', fontWeight: 'bold', offsetY: 10, color: isDarkMode ? '#e5e7eb' : '#1f2937' }, }, }, },
                colors: ['#22c55e'],
                labels: ['IKM Instansi'],
            }));
        }
    }));

    Alpine.data('barChart', () => ({
        initChart(data) {
            const isDarkMode = document.documentElement.classList.contains('dark');
            const scores = data.series[0].data;
            const barColors = scores.map(score => getGradientColor(score));
            chartInitLogic(this.$refs.bar, data, (chartData) => ({
                chart: { type: 'bar', height: 350 },
                series: chartData.series,
                xaxis: { categories: chartData.categories, labels: { style: { colors: isDarkMode ? '#e5e7eb' : '#4b5563' } } },
                yaxis: { labels: { style: { colors: isDarkMode ? '#e5e7eb' : '#4b5563' } } },
                plotOptions: { bar: { horizontal: true, borderRadius: 4, distributed: true, } },
                dataLabels: { enabled: true, offsetX: 10, style: { fontSize: '12px', fontWeight: 'bold', colors: ['#1f2937'] }, formatter: function (val) { return val.toFixed(2); } },
                legend: { show: false },
                colors: barColors,
            }));
        }
    }));

    Alpine.data('tvGaugeChart', () => ({
        initChart(data) {
            chartInitLogic(this.$refs.gauge, data, (chartData) => ({
                chart: { type: 'radialBar', height: '100%' },
                series: chartData.series,
                plotOptions: { radialBar: { hollow: { size: '65%' }, dataLabels: { name: { show: true, fontSize: '14px', color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#4b5563', offsetY: -10 }, value: { show: true, fontSize: '22px', fontWeight: 'bold', offsetY: 5, color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#1f2937' }, }, }, },
                colors: ['#22c55e'],
                labels: ['IKM Instansi'],
            }));
        }
    }));

    Alpine.data('tvBarChart', () => ({
        initChart(data) {
            const scores = data.series[0].data;
            const barColors = scores.map(score => getGradientColor(score));
            chartInitLogic(this.$refs.bar, data, (chartData) => ({
                chart: { type: 'bar', height: '100%' },
                series: chartData.series,
                xaxis: { categories: chartData.categories, labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#4b5563' } } },
                yaxis: { labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#4b5563' } } },
                plotOptions: { bar: { horizontal: true, borderRadius: 4, distributed: true, } },
                dataLabels: { enabled: true, offsetX: 10, style: { fontSize: '12px', fontWeight: 'bold', colors: ['#1f2937'] }, formatter: function (val) { return val.toFixed(2); } },
                legend: { show: false },
                colors: barColors,
            }));
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
