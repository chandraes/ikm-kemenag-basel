<div class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">
    {{-- Hero Section --}}
    <div class="relative text-center pt-16 pb-20 px-6
                bg-gradient-to-br from-indigo-500 to-purple-600 dark:from-gray-900 dark:to-gray-800
                text-white overflow-hidden shadow-lg">

        <div class="relative z-10">
            <div class="flex flex-col md:flex-row items-center justify-center gap-4 md:gap-6 mb-6">
                @if(setting('logo_path'))
                    <img src="{{ asset('storage/' . setting('logo_path')) }}" alt="Logo Instansi" class="h-16 md:h-20 w-auto">
                @endif

                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight text-white dark:text-gray-100 drop-shadow-lg">
                    {{ setting('nama_instansi', 'Survei Kepuasan Masyarakat') }}
                </h1>
            </div>

            <p class="mt-4 text-lg text-indigo-100 dark:text-gray-300 max-w-3xl mx-auto">Kami berkomitmen untuk terus meningkatkan kualitas layanan. Partisipasi Anda sangat berharga bagi kami.</p>
            <div class="mt-10">
                <a href="{{ route('survey.form') }}" class="inline-block bg-white text-indigo-600 font-bold py-3 px-10 rounded-full shadow-xl hover:bg-indigo-50 transition-all transform hover:scale-105 duration-300">
                    Ikuti Survei Sekarang
                </a>
            </div>
        </div>
    </div>

    {{-- Dasbor Utama --}}
    <div class="container mx-auto px-6 py-12">

        {{-- Filter dan Statistik Utama --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-center mb-10">
            <div class="md:col-span-1">
                <flux:select wire:model.live="periode" label="Tampilkan Data">
                    <flux:select.option value="all" label="Semua Waktu" />
                    <flux:select.option value="30d" label="30 Hari Terakhir" />
                    <flux:select.option value="90d" label="90 Hari Terakhir" />
                    <flux:select.option value="1y" label="1 Tahun Terakhir" />
                </flux:select>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg text-center">
                <div class="text-indigo-500 dark:text-indigo-400 text-3xl md:text-4xl font-bold">{{ number_format($totalResponden) }}</div>
                <div class="text-gray-500 dark:text-gray-400 mt-2 text-sm md:text-base">Total Responden</div>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg text-center">
                <div class="text-green-500 dark:text-green-400 text-3xl md:text-4xl font-bold">{{ $ikmNasional }}</div>
                <div class="text-gray-500 dark:text-gray-400 mt-2 text-sm md:text-base">IKM Instansi</div>
            </div>

            @php
                $mutuColorClass = match($mutuPelayananInstansi['grade']) {
                    'A' => 'text-green-500 dark:text-green-400',
                    'B' => 'text-sky-500 dark:text-sky-400',
                    'C' => 'text-amber-500 dark:text-amber-400',
                    'D' => 'text-red-500 dark:text-red-400',
                    default => 'text-gray-500 dark:text-gray-400',
                };
            @endphp
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg text-center">
                <div class="{{ $mutuColorClass }} text-3xl md:text-4xl font-bold">{{ $mutuPelayananInstansi['grade'] }}</div>
                <div class="text-gray-500 dark:text-gray-400 mt-2 text-sm md:text-base">{{ $mutuPelayananInstansi['description'] }}</div>
            </div>
        </div>

        {{-- Grafik-Grafik --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-10">

            <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg"
                 x-data="gaugeChart"
                 x-init='initChart(@json($dataGauge))'
                 wire:key="gauge-chart-{{ $periode }}"
            >
                <h3 class="font-bold text-lg mb-4 text-gray-700 dark:text-gray-200">Skor IKM Keseluruhan</h3>
                <div x-ref="gauge"></div>
            </div>

            {{-- PERUBAHAN: Daftar bar chart sederhana yang menyebabkan error telah dihapus. --}}
            {{-- Sekarang hanya ada grafik ApexCharts yang interaktif. --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg"
                 x-data="barChart"
                 x-init='initChart(@json($dataBarChart))'
                 wire:key="bar-chart-{{ $periode }}"
            >
                <h3 class="font-bold text-lg mb-4 text-gray-700 dark:text-gray-200">Peringkat Kepuasan per Unit Layanan</h3>
                <div x-ref="bar"></div>
            </div>

        </div>

        {{-- Ulasan Terbaru --}}
        @if($ulasanTerbaru->isNotEmpty())
        <div class="mt-16">
             <h3 class="text-2xl font-bold mb-8 text-center text-gray-700 dark:text-gray-200">Kritik & Saran Terbaru dari Masyarakat</h3>
             <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                 @foreach($ulasanTerbaru as $ulasan)
                 <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md italic">
                     <p class="text-gray-600 dark:text-gray-300 text-base leading-relaxed">"{{ Illuminate\Support\Str::limit($ulasan->kritik_saran, 150) }}"</p>
                     <p class="text-right text-sm font-semibold mt-4 text-indigo-500 dark:text-indigo-400">- {{ $ulasan->nama }}, {{ $ulasan->created_at->diffForHumans() }}</p>
                 </div>
                 @endforeach
             </div>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
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
                chart: { type: 'bar', height: 400 },
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
});
</script>
@endpush
