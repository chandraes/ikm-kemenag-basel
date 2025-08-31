<div class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    {{-- Hero Section --}}
    <div class="relative text-center bg-white dark:bg-gray-800 py-20 px-6">
        <div class="absolute top-8 left-8">
            {{-- Tampilkan logo dinamis --}}
            @if(setting('logo_path'))
                <img src="{{ asset('storage/' . setting('logo_path')) }}" alt="Logo Instansi" class="h-16 w-auto">
            @endif
        </div>

        {{-- Tampilkan nama instansi dinamis --}}
        <h1 class="text-4xl md:text-5xl font-bold text-indigo-600 dark:text-indigo-400">
            {{ setting('nama_instansi', 'Survei Kepuasan Masyarakat') }}
        </h1>

        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Kami berkomitmen untuk terus meningkatkan kualitas layanan. Partisipasi Anda sangat berharga bagi kami.</p>
        <div class="mt-8">
            <a href="{{ route('survey.form') }}" class="inline-block bg-indigo-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:bg-indigo-700 transition-transform transform hover:scale-105">
                Ikuti Survei Sekarang
            </a>
        </div>
    </div>

    {{-- Dasbor Utama --}}
    <div class="container mx-auto px-6 py-12">

        {{-- Filter dan Statistik Utama --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-center mb-10">
            <div class="md:col-span-1">
                <flux:select wire:model.live="periode" label="Tampilkan Data">
                    <flux:select.option value="all" label="Semua Waktu" />
                    <flux:select.option value="30d" label="30 Hari Terakhir" />
                    <flux:select.option value="90d" label="90 Hari Terakhir" />
                    <flux:select.option value="1y" label="1 Tahun Terakhir" />
                </flux:select>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow text-center">
                <div class="text-indigo-500 dark:text-indigo-400 text-3xl font-bold">{{ number_format($totalResponden) }}</div>
                <div class="text-gray-500 dark:text-gray-400 mt-1">Total Responden</div>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow text-center">
                <div class="text-green-500 dark:text-green-400 text-3xl font-bold">{{ $ikmNasional }}</div>
                <div class="text-gray-500 dark:text-gray-400 mt-1">IKM Nasional</div>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow text-center">
                <div class="text-sky-500 dark:text-sky-400 text-3xl font-bold">A</div>
                <div class="text-gray-500 dark:text-gray-400 mt-1">Mutu Pelayanan</div>
            </div>
        </div>

        {{-- Grafik Per Satker --}}
        <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow">
            <h3 class="text-xl font-bold mb-6">Peringkat Kepuasan per Unit Layanan</h3>
            <div class="space-y-4">
                @forelse($hasilPerSatker as $satker)
                    @if($satker->jawaban_items_count > 0)
                        <div wire:key="{{ $satker->id }}">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium">{{ $satker->nama_satker }} ({{ $satker->jawaban_items_count }} responden)</span>
                                <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $satker->skor_ikm }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                                <div class="bg-indigo-500 h-4 rounded-full" style="width: {{ $satker->skor_ikm }}%"></div>
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="text-center text-gray-500">Belum ada data survei untuk periode ini.</p>
                @endforelse
            </div>
        </div>

        {{-- Ulasan Terbaru --}}
        @if($ulasanTerbaru->isNotEmpty())
        <div class="mt-10">
             <h3 class="text-xl font-bold mb-6 text-center">Kritik & Saran Terbaru dari Masyarakat</h3>
             <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                 @foreach($ulasanTerbaru as $ulasan)
                 <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow italic">
                     <p class="text-gray-600 dark:text-gray-300">"{{ Illuminate\Support\Str::limit($ulasan->kritik_saran, 150) }}"</p>
                     <p class="text-right text-sm font-semibold mt-4 text-indigo-500">- {{ $ulasan->nama }}, {{ $ulasan->created_at->diffForHumans() }}</p>
                 </div>
                 @endforeach
             </div>
        </div>
        @endif

    </div>
</div>
