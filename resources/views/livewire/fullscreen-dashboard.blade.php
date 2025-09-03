<div class="p-4 h-screen flex flex-col" wire:key="fullscreen-dashboard-main">
    {{-- Bagian Header: Judul dan Filter --}}
    <header class="flex-shrink-0 mb-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            @if(setting('logo_path'))
            <img src="{{ asset('storage/' . setting('logo_path')) }}" alt="Logo Instansi" class="h-12 w-auto">
            @endif
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ setting('nama_instansi', 'Dasbor IKM') }}
            </h1>
        </div>
        <div class="w-64">
            <flux:select wire:model.live="periode" label="Periode Data">
                <flux:select.option value="all" label="Semua Waktu" />
                <flux:select.option value="30d" label="30 Hari Terakhir" />
                <flux:select.option value="90d" label="90 Hari Terakhir" />
                <flux:select.option value="1y" label="1 Tahun Terakhir" />
            </flux:select>
        </div>
    </header>

    <div class="flex-grow flex flex-col gap-4 min-h-0">
        <div class="h-1/2 grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- PANEL KIRI ATAS: Ringkasan Utama --}}
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-lg flex flex-col h-full"
                wire:key="summary-card">
                <div wire:ignore x-data="tvGaugeChart" x-init='initChart(@json($dataGauge))' @chart-data-updated.window="updateChart($event.detail.gaugeData)" wire:key="gauge-chart-{{ $periode }}">
                    <h3 class="font-bold text-lg text-center text-gray-700 dark:text-gray-200">Skor IKM Keseluruhan</h3>
                    <div x-ref="gauge"></div>
                </div>
                <hr class="my-3 border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div>
                        <div class="text-indigo-500 dark:text-indigo-400 text-2xl font-bold">{{
                            number_format($totalResponden) }}</div>
                        <div class="text-gray-500 dark:text-gray-400 mt-1 text-xs">Total Responden</div>
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
                        <div class="{{ $mutuColorClass }} text-2xl font-bold">{{ $mutuPelayananInstansi['grade'] ?? '-'
                            }}</div>
                        <div class="text-gray-500 dark:text-gray-400 mt-1 text-xs">{{
                            $mutuPelayananInstansi['description'] ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            {{-- PANEL KANAN ATAS: Grafik Batang --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-lg flex flex-col h-full" wire:ignore
                x-data="tvBarChart" x-init='initChart(@json($dataBarChart))' @chart-data-updated.window="updateChart($event.detail.barData)"
                wire:key="bar-chart-{{ $periode }}-{{ $sortBy }}-{{ $sortDirection }}">
                <h3 class="font-bold text-lg mb-2 text-gray-700 dark:text-gray-200 flex-shrink-0">Peringkat Kepuasan per
                    Unit Layanan</h3>
                <div class="flex-grow min-h-0">
                    <div x-ref="bar" class="h-full"></div>
                </div>
            </div>
        </div>

        {{-- BARIS BAWAH (Tinggi 50%) --}}
        <div class="h-1/2 grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden flex flex-col h-full"
                wire:key="reviews-panel">
                <div class="p-4 flex-shrink-0 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-bold text-lg text-gray-700 dark:text-gray-200">Kritik & Saran Terbaru</h3>
                </div>
                {{-- Kontainer scrollable dengan komponen autoScroller yang sama --}}
                <div class="flex-grow overflow-y-auto p-4" x-data="tvAutoScroller" x-init="init($el)">
                    <div class="space-y-4">
                        @forelse ($ulasanTerbaru as $ulasan)
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg shadow-sm">
                            <div class="mb-2">
                                <span
                                    class="inline-block bg-indigo-100 text-indigo-800 text-xs font-medium px-2 py-0.5 rounded-full dark:bg-indigo-900 dark:text-indigo-300">
                                    Untuk: {{ $ulasan->satker?->nama_satker ?? 'N/A' }}
                                </span>
                            </div>
                            <p class="italic text-sm text-gray-600 dark:text-gray-300">"{{ $ulasan->kritik_saran }}"</p>
                            <p class="text-right text-xs font-semibold mt-2 text-indigo-500 dark:text-indigo-400">- {{
                                $ulasan->nama }}, {{ $ulasan->created_at->diffForHumans() }}</p>
                        </div>
                        @empty
                        <div class="flex items-center justify-center h-full">
                            <p class="text-sm text-gray-500">Belum ada kritik dan saran.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden flex flex-col h-full"
                wire:key="details-table">
                <div class="p-4 flex-shrink-0 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-bold text-lg text-gray-700 dark:text-gray-200">Detail Kinerja per Unit Layanan</h3>
                </div>
                <div class="flex-grow overflow-y-auto" x-data="tvAutoScroller" x-init="init($el)">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10">
                            <tr>
                                <th scope="col" wire:click="setSortBy('nama_satker')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center gap-2">
                                        <span>Unit Layanan</span>
                                        @if ($sortBy === 'nama_satker')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                            class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" wire:click="setSortBy('jawaban_surveys_count')"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Responden</span>
                                        @if ($sortBy === 'jawaban_surveys_count')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                            class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" wire:click="setSortBy('skor_ikm')"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Skor IKM</span>
                                        @if ($sortBy === 'skor_ikm')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                            class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" wire:click="setSortBy('mutu_grade')"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Mutu Pelayanan</span>
                                        @if ($sortBy === 'mutu_grade')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                            class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($hasilPerSatker as $satker)
                            @if ($satker->jawaban_surveys_count > 0)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-gray-200">{{
                                    $satker->nama_satker }}</td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200 text-center">
                                    {{ $satker->jawaban_surveys_count }}</td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200 text-center font-semibold">
                                    {{ number_format($satker->skor_ikm, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    @php
                                    $badgeColorClass = match($satker->mutu['grade']) {
                                    'A' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                    'B' => 'bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-300',
                                    'C' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300',
                                    'D' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    };
                                    @endphp
                                    <span
                                        class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-medium {{ $badgeColorClass }}">
                                        {{ $satker->mutu['grade'] }} ({{ $satker->mutu['description'] }})
                                    </span>
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Belum ada data survei untuk periode ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>
