<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <flux:heading size="xl" level="1">{{ __('Kritik & Saran') }}</flux:heading>
    <flux:separator variant="subtle" />
    <div class="py-3">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="space-y-4 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            {{-- Pencarian --}}
                            <div class="col-span-1 md:col-span-2">
                                <flux:input wire:model.live.debounce.300ms="search"
                                    placeholder="Cari berdasarkan nama atau isi saran..." label="Pencarian" />
                            </div>

                            {{-- Filter Waktu --}}
                            <div>
                                <flux:select wire:model.live="filterWaktu" label="Rentang Waktu">
                                    <flux:select.option value="all" label="Semua Waktu" />
                                    <flux:select.option value="this_month" label="Bulan Ini" />
                                    <flux:select.option value="last_2_months" label="2 Bulan Terakhir" />
                                    <flux:select.option value="this_year" label="Tahun Ini" />
                                </flux:select>
                            </div>

                            {{-- Filter Satker --}}
                            <div>
                                <flux:select wire:model.live="filterSatker" label="Unit Layanan">
                                    <flux:select.option value="" label="Semua Satker" />
                                    @foreach($satkers as $satker)
                                    <flux:select.option :value="$satker->id" :label="$satker->nama_satker" />
                                    @endforeach
                                </flux:select>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            {{-- Tombol Reset --}}
                            <flux:button wire:click="resetFilters" variant="primary" color="gray"
                                icon="arrow-path-rounded-square">
                                Reset Filter
                            </flux:button>

                            <flux:button wire:click="exportPdfData" variant="primary" color="emerald"
                                icon="folder-arrow-down">
                                <span wire:loading.remove wire:target="exportPdfData">Ekspor ke PDF</span>
                                <span wire:loading wire:target="exportPdfData">Mempersiapkan...</span>
                            </flux:button>
                        </div>
                    </div>
                    {{-- Tabel Data --}}
                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr class="divide-x divide-gray-200 dark:divide-gray-600">
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/4">
                                        Responden</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/4">
                                        Unit Layanan (Satker)</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/2">
                                        Kritik & Saran</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($reviews as $review)
                                <tr class="divide-x divide-gray-200 dark:divide-gray-600">
                                    <td class="px-6 py-4 text-sm font-medium">{{ $review->nama }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $review->satker?->nama_satker ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        <p class="italic">"{{ $review->kritik_saran }}"</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $review->created_at->isoFormat('D
                                        MMM YYYY') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Tidak ada kritik dan saran yang ditemukan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Link Paginasi --}}
                    <div class="mt-4">
                        {{ $reviews->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        function generatePdf(data) {
        // Inisialisasi dokumen PDF (tetap sama)
        const doc = new window.jsPDF();

        // Judul Dokumen (tetap sama)
        doc.setFontSize(18);
        doc.text('Laporan Kritik & Saran', 14, 22);
        doc.setFontSize(11);
        doc.setTextColor(100);
        const date = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        doc.text(`Dicetak pada: ${date}`, 14, 29);

        // Siapkan data untuk tabel (tetap sama)
        const tableColumn = ["No", "Nama Responden", "Unit Layanan", "Kritik & Saran", "Tanggal"];
        const tableRows = [];

        data.forEach((item, index) => {
            const rowData = [
                index + 1,
                item.nama,
                item.satker ? item.satker.nama_satker : 'N/A',
                item.kritik_saran,
                new Date(item.created_at).toLocaleDateString('id-ID')
            ];
            tableRows.push(rowData);
        });

        // =======================================================
        // PERUBAHAN: Panggil autoTable sebagai fungsi terpisah
        // =======================================================
        window.autoTable(doc, {
            head: [tableColumn],
            body: tableRows,
            startY: 35,
            theme: 'grid',
            styles: {
                fontSize: 8,
                cellPadding: 2,
            },
            headStyles: {
                fillColor: [22, 160, 133],
                textColor: 255,
                fontStyle: 'bold',
            },
            columnStyles: {
                0: { cellWidth: 10 },
                1: { cellWidth: 35 },
                2: { cellWidth: 35 },
                3: { cellWidth: 'auto' },
                4: { cellWidth: 20 },
            }
        });

        // Simpan file PDF (tetap sama)
        doc.save('laporan-kritik-dan-saran.pdf');
    }
    </script>
    @endpush
</div>
