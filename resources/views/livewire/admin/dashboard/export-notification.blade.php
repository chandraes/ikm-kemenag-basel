<div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md mb-4">
    <h4 class="font-bold mb-2">Status Laporan Anda:</h4>
    <ul class="space-y-2">
        @forelse($exports as $export)
            <li class="flex justify-between items-center text-sm">
                <span>Laporan #{{ $export->id }} - {{ $export->created_at->diffForHumans() }}</span>
                <div class="flex items-center gap-4">
                    @if($export->status === 'completed')
                        <a href="{{ route('exports.download', $export) }}" class="text-green-500 font-semibold hover:underline">Download</a>
                    @elseif($export->status === 'processing')
                        <span class="text-amber-500">Sedang diproses...</span>
                    @else
                        <span class="text-red-500">Gagal</span>
                    @endif

                    {{-- ======================================================= --}}
                    {{-- TAMBAHKAN TOMBOL HAPUS DI SINI --}}
                    {{-- ======================================================= --}}
                    <button wire:click="deleteExport({{ $export->id }})"
                            wire:confirm="Apakah Anda yakin ingin menghapus laporan ini? File yang sudah dihapus tidak bisa dikembalikan."
                            class="text-gray-400 hover:text-red-500 transition-colors">
                        <flux:icon name="trash" class="w-4 h-4" />
                    </button>
                </div>
            </li>
        @empty
            <li>Belum ada laporan yang dibuat.</li>
        @endforelse
    </ul>
</div>
