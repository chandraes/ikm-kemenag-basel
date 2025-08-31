<div class="flex flex-1 w-full h-full flex-col gap-4 rounded-xl">
    <flux:heading size="xl" level="1">{{ __('Manajemen Kuesioner') }}</flux:heading>
    <flux:separator variant="subtle" />
    <div class="relative flex-1 h-full p-4 overflow-hidden rounded-xl">
        <div class="p-6 bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between mb-6">
                <button wire:click="create()" class="px-4 py-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                    + Tambah Pertanyaan
                </button>
            </div>

            @if($isModalOpen)
            <div class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50">
                <div class="relative w-full max-w-2xl p-8 mx-auto bg-white rounded-lg shadow-lg dark:bg-gray-900">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-200 mb-6">{{ $kuesioner_id ? 'Edit Pertanyaan' : 'Tambah Pertanyaan Baru' }}</h3>
                    <form wire:submit.prevent="triggerConfirm">
                        <div class="space-y-6">
                            <flux:textarea wire:model="pertanyaan" :label="__('Pertanyaan')" rows="3" />

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Pilihan Jawaban Skala</label>
                                <div class="p-4 space-y-4 border rounded-md dark:border-gray-700">
                                    @foreach($pilihan_jawaban as $index => $pilihan)
                                        <div class="flex items-center gap-2" wire:key="pilihan-{{ $index }}">
                                            <span class="p-2 font-mono text-sm bg-gray-100 rounded-md dark:bg-gray-800 dark:text-gray-300">{{ $pilihan['nilai'] }}</span>
                                            <div class="flex-grow">
                                                <flux:input
                                                    wire:model="pilihan_jawaban.{{ $index }}.label"
                                                    id="pilihan-label-{{ $index }}"
                                                    placeholder="Teks untuk skala {{ $pilihan['nilai'] }}"
                                                />
                                            </div>
                                            <flux:button
                                                type="button"
                                                variant="subtle"
                                                color="danger"
                                                icon="x-mark"
                                                wire:click.prevent="removePilihanJawaban({{ $index }})"
                                            />
                                        </div>
                                         @error('pilihan_jawaban.' . $index . '.label') <span class="block text-sm text-red-600">{{ $message }}</span> @enderror
                                    @endforeach

                                    <div class="pt-2">
                                       <flux:button type="button" variant="ghost" icon="plus" wire:click.prevent="addPilihanJawaban">
                                           {{ __('Tambah Pilihan') }}
                                       </flux:button>
                                    </div>
                                </div>
                            </div>

                            <flux:input wire:model="urutan" :label="__('Urutan')" type="number" />
                        </div>
                        <div class="flex justify-end mt-8 space-x-3">
                            <button type="button" wire:click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">Batal</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <div class="flex flex-col gap-4 my-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full md:w-1/4">
                    <flux:select wire:model.live="perPage">
                        <flux:select.option :value="5" :label="__('5')" />
                        <flux:select.option :value="10" :label="__('10')" />
                        <flux:select.option :value="25" :label="__('25')" />
                        <flux:select.option :value="50" :label="__('50')" />
                    </flux:select>
                </div>
                <div class="w-full md:w-1/3">
                    <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Cari pertanyaan..."/>
                </div>
            </div>

            <div class="overflow-hidden bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                                <th wire:click="sortingBy('urutan')" class="w-16 px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400">
                                    <div class="flex items-center gap-1.5">
                                        <span>Urutan</span>
                                        @if ($sortBy === 'urutan')
                                            <flux:icon :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortingBy('pertanyaan')" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-400">
                                    <div class="flex items-center gap-1.5">
                                        <span>Pertanyaan</span>
                                        @if ($sortBy === 'pertanyaan')
                                            <flux:icon :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                                {{-- HEADER BARU --}}
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                    Pilihan Jawaban
                                </th>
                                <th class="w-40 px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($kuesioners as $kuesioner)
                            <tr class="text-gray-900 hover:bg-gray-50 divide-x divide-gray-200 dark:text-gray-200 dark:hover:bg-gray-900/75 dark:divide-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-center">{{ $kuesioner->urutan }}</td>
                                <td class="px-6 py-4 whitespace-normal">{{ $kuesioner->pertanyaan }}</td>
                                {{-- SEL DATA BARU --}}
                                <td class="px-6 py-4 text-sm whitespace-normal dark:text-gray-300">
                                    <div class="flex flex-col gap-1">
                                        @if(is_array($kuesioner->pilihan_jawaban))
                                            @foreach($kuesioner->pilihan_jawaban as $pilihan)
                                                <div>
                                                    <span class="font-semibold">{{ $pilihan['nilai'] }}.</span>
                                                    <span>{{ $pilihan['label'] }}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                    <flux:button
                                        variant="primary"
                                        color="indigo"
                                        wire:click="edit({{ $kuesioner->id }})"
                                        size="sm"
                                        icon="pencil-square"
                                    />
                                    <flux:button
                                        variant="primary"
                                        color="danger"
                                        wire:click="confirmDelete({{ $kuesioner->id }})"
                                        size="sm"
                                        icon="trash"
                                    />
                                </td>
                            </tr>
                            @empty
                            <tr>
                                {{-- COLSPAN DISESUAIKAN --}}
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data kuesioner yang ditemukan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $kuesioners->links() }}
            </div>
        </div>
    </div>
</div>
