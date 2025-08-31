<div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
    <flux:heading size="xl">{{ __('Pengaturan Aplikasi') }}</flux:heading>
    <flux:separator class="my-6" />

    <form wire:submit.prevent="save" class="space-y-6">
        {{-- Nama Instansi --}}
        <div>
            <flux:input wire:model="nama_instansi" :label="__('Nama Instansi')" />
            @error('nama_instansi') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- Upload Logo --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Logo Instansi</label>
            <div class="flex items-center gap-6">
                <div>
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" class="w-24 h-24 object-contain rounded-md bg-gray-100 dark:bg-gray-700">
                    @elseif ($existingLogo)
                        <img src="{{ asset('storage/' . $existingLogo) }}" class="w-24 h-24 object-contain rounded-md bg-gray-100 dark:bg-gray-700">
                    @else
                        <div class="w-24 h-24 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-md text-gray-400">
                            <flux:icon name="photo" class="w-12 h-12" />
                        </div>
                    @endif
                </div>
                <div>
                    <input type="file" wire:model="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100"/>
                    <div wire:loading wire:target="logo" class="text-sm text-gray-500 mt-2">Uploading...</div>
                    @error('logo') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="pt-4">
            <flux:button type="submit" variant="primary">
                {{ __('Simpan Pengaturan') }}
            </flux:button>
        </div>
    </form>
</div>
