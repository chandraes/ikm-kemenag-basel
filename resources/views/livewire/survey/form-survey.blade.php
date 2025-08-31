<div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-3xl p-4 sm:p-8 mx-auto my-12">

        {{-- Langkah 0: Isi Data Diri --}}
        @if ($langkah === 0)
            <div class="p-8 bg-white rounded-xl shadow-lg dark:bg-gray-800 animate-fade-in">
                <div class="text-center">
                    <flux:icon name="clipboard-document-list" class="w-16 h-16 mx-auto text-indigo-500" />
                    <flux:heading size="2xl" class="mt-4">{{ __('Survei Kepuasan Masyarakat') }}</flux:heading>
                    <flux:text size="lg" class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ __('Terima kasih atas partisipasi Anda. Mohon lengkapi data berikut sebelum memulai survei.') }}
                    </flux:text>
                </div>

                <div class="mt-8 space-y-6">
                    <div>
                        <flux:select wire:model="satker_id" :label="__('Pilih Satuan Kerja / Unit Layanan *')" placeholder="-- Pilih salah satu --">
                            @foreach($satkers as $satker)
                                <flux:select.option :value="$satker->id" :label="$satker->nama_satker" />
                            @endforeach
                        </flux:select>
                        @error('satker_id') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <flux:input wire:model="nama" :label="__('Nama Lengkap *')" />
                            @error('nama') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <flux:input wire:model="usia" :label="__('Usia *')" type="number" />
                             @error('usia') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <flux:input wire:model="email" :label="__('Email (Opsional)')" type="email" />
                        @error('email') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <flux:textarea wire:model="alamat_lengkap" :label="__('Alamat Lengkap *')" rows="3" />
                         @error('alamat_lengkap') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                     <div>
                        <flux:textarea wire:model="keterangan_keperluan" :label="__('Keterangan Keperluan Layanan *')" rows="3" />
                        @error('keterangan_keperluan') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <flux:button wire:click="mulaiSurvey" variant="primary">
                        {{ __('Lanjutkan & Mulai Survei') }}
                    </flux:button>
                </div>
            </div>
        @endif

        {{-- Langkah 1: Isi Kuesioner --}}
        @if ($langkah === 1)
            <div class="relative p-8 bg-white rounded-xl shadow-lg dark:bg-gray-800 animate-fade-in-up">
                <div class="absolute top-4 left-4">
                    <flux:button wire:click="kembali" variant="ghost" icon="arrow-left">
                        {{ __('Kembali') }}
                    </flux:button>
                </div>

                <flux:heading size="xl" class="mb-6 text-center pt-8">
                    {{ $satkers->find($satker_id)->nama_satker }}
                </flux:heading>
                <div class="space-y-10">
                    @foreach($kuesioners as $index => $kuesioner)
                        <div
                            id="question-{{ $kuesioner->id }}"
                            @class([
                                'p-6 rounded-lg transition-all duration-300',
                                'question-box', // Kelas dasar dari app.css
                                'invalid' => $errors->has('jawaban.' . $kuesioner->id) // Tambah kelas 'invalid' jika ada error
                            ])
                        >
                            <flux:text class="font-semibold text-lg dark:text-gray-200">{{ $kuesioner->urutan }}. {{ $kuesioner->pertanyaan }}</flux:text>

                            <div
                                class="mt-4 flex items-stretch gap-2 sm:gap-3"
                                style="--item-count: {{ count($kuesioner->pilihan_jawaban) }};"
                            >
                                @foreach($kuesioner->pilihan_jawaban as $pilihan)
                                    @php
                                        $totalOptions = count($kuesioner->pilihan_jawaban);
                                        $style = $this->getStyleForValue($pilihan['nilai'], $totalOptions);
                                        $isSelected = ($jawaban[$kuesioner->id] ?? null) == $pilihan['nilai'];
                                    @endphp

                                    <label
                                        wire:key="jawaban-{{ $kuesioner->id }}-{{ $pilihan['nilai'] }}"
                                        @class([
                                            'flex flex-1 flex-col items-center justify-center p-2 sm:p-3 border rounded-lg cursor-pointer transition-all duration-200 space-y-2',
                                            'shadow-md scale-105' => $isSelected,
                                            'bg-white text-gray-700 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' => ! $isSelected,
                                            'bg-red-600 border-red-600 text-white' => $isSelected && $style['color'] === 'red-600',
                                            'bg-amber-500 border-amber-500 text-white' => $isSelected && $style['color'] === 'amber-500',
                                            'bg-sky-500 border-sky-500 text-white' => $isSelected && $style['color'] === 'sky-500',
                                            'bg-teal-500 border-teal-500 text-white' => $isSelected && $style['color'] === 'teal-500',
                                            'bg-green-500 border-green-500 text-white' => $isSelected && $style['color'] === 'green-500',
                                            'bg-emerald-500 border-emerald-500 text-white' => $isSelected && $style['color'] === 'emerald-500',
                                        ])
                                    >
                                        <input type="radio" wire:model.live="jawaban.{{ $kuesioner->id }}" value="{{ $pilihan['nilai'] }}" class="sr-only">

                                        @if (Str::startsWith($style['icon'], 'icons.'))
                                            <x-dynamic-component :component="$style['icon']" class="w-7 h-7 sm:w-8 sm:h-8" />
                                        @else
                                            <flux:icon name="{{ $style['icon'] }}" class="w-7 h-7 sm:w-8 sm:h-8" />
                                        @endif

                                        <span class="font-medium text-center break-words leading-tight
                                                     text-[clamp(0.55rem,calc(12vw/var(--item-count)),0.875rem)]">
                                            {{ $pilihan['label'] }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                 @error('jawaban.*') <span class="block w-full p-3 mt-6 text-center text-sm text-red-700 bg-red-100 rounded-lg">{{ $message }}</span> @enderror
                <div class="mt-10 text-center">
                    <flux:button wire:click="simpanSurvey" variant="primary">
                        {{ __('Lanjutkan') }}
                    </flux:button>
                </div>
            </div>
        @endif

        {{-- Langkah 2: Isi Kritik & Saran --}}
        @if ($langkah === 2)
            <div class="relative p-8 bg-white rounded-xl shadow-lg dark:bg-gray-800 animate-fade-in-up">
                <div class="absolute top-4 left-4">
                    <flux:button wire:click="kembali" variant="ghost" icon="arrow-left">
                        {{ __('Kembali') }}
                    </flux:button>
                </div>

                <div class="text-center pt-8">
                    <flux:icon name="chat-bubble-left-ellipsis" class="w-16 h-16 mx-auto text-indigo-500" />
                    <flux:heading size="xl" class="mt-4">{{ __('Satu Langkah Terakhir') }}</flux:heading>
                    <flux:text size="lg" class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ __('Apakah Anda memiliki kritik atau saran untuk perbaikan layanan kami di masa mendatang? (Opsional)') }}
                    </flux:text>
                </div>
                <div class="mt-8">
                    <flux:textarea wire:model="kritik_saran" placeholder="Tuliskan masukan Anda di sini..." rows="5" />
                    @error('kritik_saran') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-center items-center mt-8 gap-4">
                     <flux:button wire:click="finalkanSurvey" variant="ghost">
                        {{ __('Lewati') }}
                    </flux:button>
                    <flux:button wire:click="finalkanSurvey" variant="primary">
                        {{ __('Kirim Survei') }}
                    </flux:button>
                </div>
            </div>
        @endif

        {{-- Langkah 3: Selesai --}}
        @if ($langkah === 3)
            <div class="p-8 text-center bg-white rounded-xl shadow-lg dark:bg-gray-800 animate-fade-in-up">
                <flux:icon name="check-circle" class="w-20 h-20 mx-auto text-green-500" />
                <flux:heading size="2xl" class="mt-4">{{ __('Terima Kasih!') }}</flux:heading>
                <flux:text size="lg" class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ __('Jawaban survei Anda telah berhasil kami terima. Partisipasi Anda sangat berarti untuk meningkatkan kualitas layanan kami.') }}
                </flux:text>
                <div class="mt-8">
                     <flux:button wire:click="surveyLagi" variant="ghost">
                        {{ __('Isi Survei Lagi') }}
                    </flux:button>
                </div>
            </div>
        @endif

    </div>
</div>

@script
<script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('focus-on-question', ({ elementId }) => {
            const element = document.getElementById(elementId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
</script>
@endscript
