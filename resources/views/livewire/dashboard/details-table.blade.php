{{-- PERUBAHAN: Hapus div pembungkus dan judul, jadikan kartu sebagai elemen root --}}
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden flex flex-col h-full">
    <div class="p-4 flex-shrink-0 border-b border-gray-200 dark:border-gray-700">
        <h3 class="font-bold text-lg text-gray-700 dark:text-gray-200">Detail Kinerja per Unit Layanan</h3>
    </div>
    {{-- Kontainer scrollable untuk tabel --}}
    <div class="flex-grow overflow-y-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10">
                <tr>
                    <th scope="col" wire:click="setSortBy('nama_satker')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                        <div class="flex items-center gap-2">
                            <span>Unit Layanan</span>
                            @if ($sortBy === 'nama_satker')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                            @endif
                        </div>
                    </th>
                    <th scope="col" wire:click="setSortBy('jawaban_surveys_count')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                        <div class="flex items-center justify-center gap-2">
                            <span>Responden</span>
                            @if ($sortBy === 'jawaban_surveys_count')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                            @endif
                        </div>
                    </th>
                    <th scope="col" wire:click="setSortBy('skor_ikm')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                        <div class="flex items-center justify-center gap-2">
                            <span>Skor IKM</span>
                            @if ($sortBy === 'skor_ikm')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                            @endif
                        </div>
                    </th>
                    <th scope="col" wire:click="setSortBy('mutu_grade')" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                        <div class="flex items-center justify-center gap-2">
                            <span>Mutu Pelayanan</span>
                            @if ($sortBy === 'mutu_grade')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4" />
                            @endif
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($hasilPerSatker as $satker)
                    @if ($satker->jawaban_surveys_count > 0)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $satker->nama_satker }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200 text-center">{{ $satker->jawaban_surveys_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200 text-center font-semibold">{{ number_format($satker->skor_ikm, 2) }}</td>
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
                                <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-medium {{ $badgeColorClass }}">
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
