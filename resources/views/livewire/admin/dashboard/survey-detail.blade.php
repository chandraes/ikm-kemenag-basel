<div>
     <flux:heading size="xl" level="1">{{ __('Detail Survei') }}</flux:heading>
    <flux:separator variant="subtle" />
     <flux:button size="sm" href="{{ route('admin.responden') }}" class="mt-3" variant="primary" color="gray" wire:navigate>
                &larr; Kembali
            </flux:button>
    <div class="py-2">
        <div class="max-w-4xl mx-auto sm:px-2 lg:px-2">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100 space-y-6">
                    <div>
                        <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-2">Data Responden</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div class="font-medium text-gray-500">Nama Lengkap:</div>
                            <div>{{ $survey->nama }}</div>

                            <div class="font-medium text-gray-500">Unit Layanan:</div>
                            <div>{{ $survey->satker?->nama_satker }}</div>

                            <div class="font-medium text-gray-500">Tanggal Survei:</div>
                            <div>{{ $survey->created_at->isoFormat('D MMMM YYYY, HH:mm') }}</div>

                            <div class="font-medium text-gray-500">Usia:</div>
                            <div>{{ $survey->usia }} tahun</div>

                            <div class="font-medium text-gray-500">Jenis Kelamin:</div>
                            <div>{{ $survey->jenis_kelamin }}</div>

                            <div class="font-medium text-gray-500">Pendidikan:</div>
                            <div>{{ $survey->pendidikan }}</div>

                            <div class="font-medium text-gray-500">Pekerjaan:</div>
                            <div>{{ $survey->pekerjaan === 'Lainnya' ? $survey->pekerjaan_lainnya : $survey->pekerjaan }}</div>
                        </div>
                    </div>

                    {{-- Jawaban Kuesioner --}}
                    <div>
                        <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-2">Jawaban Kuesioner</h3>
                        <ul class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                            @foreach ($survey->jawabanItems->sortBy('kuesioner.urutan') as $item)
                                <li class="text-sm">
                                    <p class="text-gray-700 dark:text-gray-300">{{ $loop->iteration }}. {{ $item->kuesioner?->pertanyaan }}</p>
                                    <p class="font-semibold text-indigo-700 dark:text-indigo-400 mt-1 pl-4">Jawaban: ({{ $item->jawaban_nilai }}) {{ $item->jawaban_label }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Kritik & Saran --}}
                    @if ($survey->kritik_saran)
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-2">Kritik & Saran</h3>
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <p class="text-sm italic text-gray-600 dark:text-gray-400">"{{ $survey->kritik_saran }}"</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
