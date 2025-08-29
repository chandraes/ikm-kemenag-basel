<div class="flex flex-1 w-full h-full flex-col gap-4 rounded-xl">
    <flux:heading size="xl" level="1">{{ __('Manajemen Satker') }}</flux:heading>
    <flux:separator variant="subtle" />
    <div class="relative flex-1 h-full p-4 overflow-hidden rounded-xl">
        {{-- Latar belakang utama halaman disesuaikan --}}
        <div class="p-6 bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold text-gray-800"></h2>
                <button wire:click="create()" class="px-4 py-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                    + Tambah Satker
                </button>
            </div>

            @if($isModalOpen)
            <div class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50">
                {{-- Latar belakang & teks modal disesuaikan --}}
                <div class="relative w-full max-w-lg p-8 mx-auto bg-white rounded-lg shadow-lg dark:bg-gray-900">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-200 mb-4">{{ $satker_id ? 'Edit Satker' : 'Tambah Satker Baru' }}</h3>
                    <form wire:submit.prevent="triggerConfirm">
                        <div>
                            <flux:input type="text" wire:model="nama_satker" id="nama_satker"
                                :label="__('Nama Satker')"></flux:input>
                            @error('nama_satker') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex justify-end mt-6 space-x-3">
                             {{-- Tombol Batal disesuaikan --}}
                            <button type="button" wire:click="closeModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <div class="flex flex-col gap-4 my-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full md:w-1/4">
                    <flux:select wire:model.live="perPage">
                        <flux:select.option :value="10" :label="__('10')" />
                        <flux:select.option :value="25" :label="__('25')" />
                        <flux:select.option :value="50" :label="__('50')" />
                        <flux:select.option :value="100" :label="__('100')" />
                    </flux:select>
                </div>

                <div class="w-full md:w-1/3">
                    <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Cari nama satker..."/>
                </div>
            </div>

            {{-- Wrapper tabel disesuaikan --}}
            <div class="overflow-hidden bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        {{-- Header tabel disesuaikan --}}
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">No</th>
                                <th wire:click="sortingBy('nama_satker')" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400">
                                    <div class="flex items-center gap-1.5">
                                        <span>Nama Satker</span>
                                        @if ($sortBy === 'nama_satker')
                                            <flux:icon :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        {{-- Body tabel disesuaikan --}}
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($satkers as $satker)
                            <tr class="text-gray-900 hover:bg-gray-50 divide-x divide-gray-200 dark:text-gray-200 dark:hover:bg-gray-900/75 dark:divide-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $satkers->firstItem() + $loop->index }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $satker->nama_satker }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                    <flux:button class="me-3" variant="primary" wire:click="edit({{ $satker->id }})"
                                        size="sm" icon="pencil-square"></flux:button>
                                    <flux:button variant="danger" wire:click="confirmDelete({{ $satker->id }})" size="sm"
                                        icon="trash"></flux:button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data satker yang ditemukan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $satkers->links() }}
            </div>
        </div>
    </div>
</div>
