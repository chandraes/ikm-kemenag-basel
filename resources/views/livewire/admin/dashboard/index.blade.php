<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <flux:heading size="xl" level="1">{{ __('Dashboard') }}</flux:heading>
    <flux:separator variant="subtle" />
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-2 lg:px-2">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-2 text-gray-900 dark:text-gray-100">

                    {{-- Di sini nanti kita akan letakkan filter --}}
                    <div class="container mx-auto px-2 py-2">
                        {{-- Header Dasbor: Filter dan Tombol Mode TV --}}
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-64">
                                    <flux:select wire:model.live="periode" label="Periode Data">
                                        <flux:select.option value="all" label="Semua Waktu" />
                                        <flux:select.option value="30d" label="30 Hari Terakhir" />
                                        <flux:select.option value="90d" label="90 Hari Terakhir" />
                                        <flux:select.option value="1y" label="1 Tahun Terakhir" />
                                    </flux:select>
                                </div>
                                <div class="mt-6">
                                    <flux:button wire:click="openShareModal" variant="primary" color="green"
                                        icon="qr-code">
                                        Bagikan Survei
                                    </flux:button>
                                </div>
                                <a href="{{ route('landing.full-screen') }}" target="_blank"
                                    class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition">
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
                            <div
                                class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg flex flex-col gap-4">
                                <div x-data="gaugeChart" x-init='initChart(@json($dataGauge))' @chart-data-updated.window="updateChart($event.detail.gaugeData)"
                                    wire:key="gauge-chart-{{ $periode }}">
                                    <h3 class="font-bold text-lg text-center text-gray-700 dark:text-gray-200">Skor IKM
                                        Keseluruhan</h3>
                                    <div x-ref="gauge"></div>
                                </div>
                                <hr class="border-gray-200 dark:border-gray-700">
                                <div class="grid grid-cols-2 gap-4 text-center">
                                    <div>
                                        <div class="text-indigo-500 dark:text-indigo-400 text-3xl font-bold">{{
                                            number_format($totalResponden) }}</div>
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
                                        <div class="{{ $mutuColorClass }} text-3xl font-bold">{{
                                            $mutuPelayananInstansi['grade'] ?? '-' }}</div>
                                        <div class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{
                                            $mutuPelayananInstansi['description'] ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Grafik Batang Peringkat Satker (2/3) --}}
                            <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg"
                                x-data="barChart" x-init='initChart(@json($dataBarChart))' @chart-data-updated.window="updateChart($event.detail.barData)"
                                wire:key="bar-chart-{{ $periode }}-{{ now() }}">
                                <h3 class="font-bold text-lg mb-4 text-gray-700 dark:text-gray-200">Peringkat Kepuasan
                                    per Unit Layanan</h3>
                                <div x-ref="bar" style="height: 400px;"></div>
                            </div>
                        </div>

                        {{-- Baris untuk Tabel Detail --}}
                        <div class="mt-6">
                            <livewire:dashboard.details-table :periode="$periode" :key="'details-table-'.$periode" />
                        </div>

                    </div>

                    @if ($isShareModalOpen)
                    <div x-data
                        class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50">
                        <div class="relative w-full max-w-md p-6 mx-auto bg-white rounded-lg shadow-lg dark:bg-gray-900"
                            @click.outside="$wire.closeShareModal()">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-200 mb-4">Bagikan Link Survei
                                Global</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                                Gunakan QR code ini untuk responden yang akan memilih sendiri unit layanannya.
                            </p>

                            {{-- Tempat untuk QR Code --}}
                            <div class="flex justify-center items-center p-4 bg-white rounded-md">
                                <canvas id="qrcode-canvas"></canvas>
                            </div>

                            <div class="mt-6">
                                <a id="download-qr" href="#" download="qrcode-survei-global.png"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                                    <flux:icon name="arrow-down-tray" class="w-5 h-5" />
                                    Unduh QR Code
                                </a>
                            </div>

                            <div class="flex justify-end mt-6">
                                <button type="button" wire:click="closeShareModal()"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
