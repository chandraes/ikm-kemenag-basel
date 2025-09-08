<div class="space-y-4">
    {{-- BARIS FILTER & PENCARIAN --}}
    <livewire:admin.dashboard.export-notification />
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
        {{-- Pencarian --}}
        <div class="col-span-1 md:col-span-2">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama atau keperluan..."
                label="Pencarian" />
        </div>

        {{-- Filter Satker --}}
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Satker</label>
            <div x-data="{ open: false }" @click.outside="open = false" class="relative mt-1">
                <input type="text" wire:model.live.debounce.300ms="searchSatker" @focus="open = true"
                    placeholder="Semua Satker"
                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    autocomplete="off">
                <div x-show="open" x-transition
                    class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 rounded-md shadow-lg max-h-60 overflow-auto">
                    <ul>
                        @foreach($satkers as $satker)
                        <li wire:click="selectSatker({{ $satker->id }}, '{{ $satker->nama_satker }}')"
                            @click="open = false"
                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-gray-700">{{
                            $satker->nama_satker }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- FILTER PENDIDIKAN (SEARCHABLE) --}}
        {{-- ======================================================= --}}
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pendidikan</label>
            <div x-data="{ open: false }" @click.outside="open = false" class="relative mt-1">
                <input type="text" wire:model.live.debounce.300ms="searchPendidikan" @focus="open = true"
                    placeholder="Semua Pendidikan"
                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    autocomplete="off">
                <div x-show="open" x-transition
                    class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 rounded-md shadow-lg max-h-60 overflow-auto">
                    <ul>
                        @foreach($filteredPendidikanOptions as $option)
                        <li wire:click="selectPendidikan('{{ $option }}')" @click="open = false"
                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-gray-700">{{ $option }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- FILTER PEKERJAAN (SEARCHABLE) --}}
        {{-- ======================================================= --}}
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pekerjaan</label>
            <div x-data="{ open: false }" @click.outside="open = false" class="relative mt-1">
                <input type="text" wire:model.live.debounce.300ms="searchPekerjaan" @focus="open = true"
                    placeholder="Semua Pekerjaan"
                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    autocomplete="off">
                <div x-show="open" x-transition
                    class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 rounded-md shadow-lg max-h-60 overflow-auto">
                    <ul>
                        @foreach($filteredPekerjaanOptions as $option)
                        <li wire:click="selectPekerjaan('{{ $option }}')" @click="open = false"
                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-gray-700">{{ $option }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-span-2">
            <flux:input wire:model.live="startDate" type="date" label="Dari Tanggal" />
        </div>
        <div class="col-span-2">
            <flux:input wire:model.live="endDate" type="date" label="Sampai Tanggal" />
        </div>
        {{-- Tombol Reset --}}
        <div class="col-span-1">
            <flux:button wire:click="resetFilters" variant="primary" color="gray" class="w-full">
                Reset Filter
            </flux:button>
        </div>
        <div class="col-span-1">
        <flux:button wire:click="export" variant="primary" color="green" class="w-full">
            <span wire:loading.remove wire:target="export">
                Download
            </span>
            <span wire:loading wire:target="export">
                Memulai...
            </span>
        </flux:button>
    </div>
    </div>

    {{-- BARIS FILTER TANGGAL & JUMLAH DATA --}}
    <div class="flex justify-end items-center">
        <div>
            <flux:select wire:model.live="perPage" label="Data per halaman">
                <flux:select.option value="5" label="5" />
                <flux:select.option value="10" label="10" />
                <flux:select.option value="25" label="25" />
                <flux:select.option value="50" label="50" />
                <flux:select.option value="100" label="100" />
            </flux:select>
        </div>
    </div>

    {{-- TABEL DATA --}}
    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr class="divide-x divide-gray-200 dark:divide-gray-600">
                    <th wire:click="sortBy('nama')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Nama Responden @if($sortField === 'nama')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</th>
                    <th wire:click="sortBy('satker_id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Unit Layanan @if($sortField === 'satker_id')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</th>
                    <th wire:click="sortBy('pendidikan')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Pendidikan @if($sortField === 'pendidikan')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</th>
                    <th wire:click="sortBy('pekerjaan')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Pekerjaan @if($sortField === 'pekerjaan')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</th>
                    <th wire:click="sortBy('nilai_ikm')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Nilai IKM @if($sortField === 'nilai_ikm')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</th>
                    <th wire:click="sortBy('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Tanggal Survei @if($sortField === 'created_at')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</th>
                    <th class="relative px-6 py-3"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($surveys as $survey)
                <tr class="divide-x divide-gray-200 dark:divide-gray-600">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $survey->nama }}</td>
                    <td class="px-6 py-4 text-sm">{{ $survey->satker?->nama_satker ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $survey->pendidikan }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $survey->pekerjaan === 'Lainnya' ?
                        $survey->pekerjaan_lainnya : $survey->pekerjaan }}</td>
                    @php
                        // Kita tetap hitung nilainya di sini sekali
                        $nilaiIkm = $survey->hitungNilaiIkm();
                    @endphp
                    <td @class([
                        'px-6 py-4 whitespace-nowrap text-sm', // Kelas dasar
                        'text-red-600 dark:text-red-400 font-bold' => $nilaiIkm < 65,
                        'text-sky-600 dark:text-sky-400 font-semibold' => $nilaiIkm >= 65 && $nilaiIkm < 81.25,
                        'text-green-600 dark:text-green-400 font-semibold' => $nilaiIkm >= 81.25,
                    ])>
                        {{ number_format($nilaiIkm, 2) }}
                    </td>
                        <td class="px-6 py-4 text-sm">{{ $survey->created_at->isoFormat('D MMMM YYYY,
                            HH:mm') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.surveys.show', $survey) }}" wire:navigate
                                class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                Detail
                            </a>
                        </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                        Tidak ada data yang cocok dengan filter Anda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- LINK PAGINASI --}}
    <div>
        {{ $surveys->links() }}
    </div>


</div>
