<div class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">
    {{-- Hero Section (Tidak Berubah) --}}
  <div class="relative text-center pt-16 pb-20 px-6
                bg-gradient-to-br from-indigo-500 to-purple-600 dark:from-gray-900 dark:to-gray-800
                text-white overflow-hidden shadow-lg">

        <div class="relative z-10">
            {{-- ========================================================= --}}
            {{-- PERUBAHAN: Bungkus semua konten tengah dalam satu div --}}
            {{-- ========================================================= --}}
            <div class="flex flex-col items-center gap-6">

                {{-- Baris 1: Logo dan Judul --}}
                <div class="flex flex-col md:flex-row items-center justify-center gap-4 md:gap-6">
                    @if(setting('logo_path'))
                        <img src="{{ asset('storage/' . setting('logo_path')) }}" alt="Logo Instansi" class="h-16 md:h-20 w-auto">
                    @endif

                    <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight text-white dark:text-gray-100 drop-shadow-lg">
                        {{ setting('nama_instansi', 'Survei Kepuasan Masyarakat') }}
                    </h1>
                </div>

                {{-- Baris 2: Deskripsi --}}
                {{-- Margin atas (mt-4) dihapus karena sudah diatur oleh `gap-6` dari pembungkus --}}
                <p class="text-lg text-indigo-100 dark:text-gray-300 max-w-3xl mx-auto">Kami berkomitmen untuk terus meningkatkan kualitas layanan. Partisipasi Anda sangat berharga bagi kami.</p>

                {{-- Baris 3: Tombol Aksi --}}
                {{-- Margin atas (mt-10) dihapus karena sudah diatur oleh `gap-6` dari pembungkus --}}
                <div>
                    <a href="{{ route('survey.form') }}" class="inline-block bg-white text-indigo-600 font-bold py-3 px-10 rounded-full shadow-xl hover:bg-indigo-50 transition-all transform hover:scale-105 duration-300">
                        Ikuti Survei Sekarang
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- Dasbor Utama --}}
    <div class="container mx-auto px-6 py-12">

        {{-- Header Dasbor: Filter dan Tombol Mode TV --}}
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Dasbor Real-time</h2>
            <div class="flex items-center gap-4">
                <div class="w-64">
                    <flux:select wire:model.live="periode" label="Periode Data">
                        <flux:select.option value="all" label="Semua Waktu" />
                        <flux:select.option value="30d" label="30 Hari Terakhir" />
                        <flux:select.option value="90d" label="90 Hari Terakhir" />
                        <flux:select.option value="1y" label="1 Tahun Terakhir" />
                    </flux:select>
                </div>
                <a href="{{ route('landing.full-screen') }}" target="_blank" class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition">
                    <flux:icon name="arrows-pointing-out" class="w-5 h-5" />
                    <span>Mode TV</span>
                </a>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- PERUBAHAN: Layout Dasbor Baru Sesuai Permintaan --}}
        {{-- ========================================================= --}}

        {{-- Baris untuk Grafik --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Kartu Ringkasan Gabungan (1/3) --}}
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg flex flex-col gap-4">
                <div x-data="gaugeChart" x-init='initChart(@json($dataGauge))' wire:key="gauge-chart-{{ $periode }}">
                    <h3 class="font-bold text-lg text-center text-gray-700 dark:text-gray-200">Skor IKM Keseluruhan</h3>
                    <div x-ref="gauge"></div>
                </div>
                <hr class="border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div>
                        <div class="text-indigo-500 dark:text-indigo-400 text-3xl font-bold">{{ number_format($totalResponden) }}</div>
                        <div class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Total Responden</div>
                    </div>
                    <div>
                         @php
                            $mutuColorClass = match($mutuPelayananInstansi['grade'] ?? 'default') {
                                'A' => 'text-green-500 dark:text-green-400',
                                'B' => 'text-sky-500 dark:text-sky-400',
                                'C' => 'text-amber-500 dark:text-amber-400',
                                'D' => 'text-red-500 dark:text-red-400',
                                default => 'text-gray-500 dark:text-gray-400',
                            };
                        @endphp
                        <div class="{{ $mutuColorClass }} text-3xl font-bold">{{ $mutuPelayananInstansi['grade'] ?? '-' }}</div>
                        <div class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ $mutuPelayananInstansi['description'] ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            {{-- Grafik Batang Peringkat Satker (2/3) --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg"
                 x-data="barChart"
                 x-init='initChart(@json($dataBarChart))'
                 wire:key="bar-chart-{{ $periode }}-{{ now() }}"
            >
                <h3 class="font-bold text-lg mb-4 text-gray-700 dark:text-gray-200">Peringkat Kepuasan per Unit Layanan</h3>
                <div x-ref="bar" style="height: 400px;"></div>
            </div>
        </div>

        {{-- Baris untuk Tabel Detail --}}
        <div class="mt-6">
             <livewire:dashboard.details-table :periode="$periode" :key="'details-table-'.$periode" />
        </div>

        {{-- Ulasan Terbaru (Tidak Berubah) --}}
        @if($ulasanTerbaru->isNotEmpty())
        <div class="mt-16">
            <h3 class="text-2xl font-bold mb-8 text-center text-gray-700 dark:text-gray-200">Kritik & Saran Terbaru dari Masyarakat</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                 @foreach($ulasanTerbaru as $ulasan)
                 <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md flex flex-col">
                     <div class="mb-3">
                         <span class="inline-block bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-indigo-900 dark:text-indigo-300">
                             Untuk: {{ $ulasan->satker?->nama_satker ?? 'N/A' }}
                         </span>
                     </div>
                     <div class="flex-grow italic">
                        <p class="text-gray-600 dark:text-gray-300 text-base leading-relaxed">"{{ Illuminate\Support\Str::limit($ulasan->kritik_saran, 150) }}"</p>
                     </div>
                     <p class="text-right text-sm font-semibold mt-4 text-indigo-500 dark:text-indigo-400">- {{ $ulasan->nama }}, {{ $ulasan->created_at->diffForHumans() }}</p>
                 </div>
                 @endforeach
             </div>
        </div>
        @endif

    </div>
</div>
