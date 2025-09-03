<div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-3xl p-4 sm:p-8 mx-auto my-12" x-data="surveyForm(getSurveyInitialData())" x-init="init()"
        @focus-on-question.window="document.getElementById($event.detail.elementId)?.scrollIntoView({ behavior: 'smooth', block: 'center' })">
        <div x-show="isLoading" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;"
            {{-- Mencegah FOUC (flash of unstyled content) --}}>
            <div class="flex items-center space-x-3 text-white text-xl font-semibold">
                {{-- Spinner SVG untuk animasi --}}
                <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span>Memproses...</span>
            </div>
        </div>
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
                {{-- Input Satker (Full Client-side Search) --}}
                <div>
                    <label for="search-satker" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Pilih Satuan Kerja / Unit Layanan *
                    </label>
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative mt-1">
                        <input id="search-satker" type="text" x-model="searchSatker" @focus="open = true"
                            @input="satker_id = null" placeholder="Pilih atau ketik unit layanan..."
                            class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            autocomplete="off" :readonly="satker_id" :disabled="isSatkerLocked">

                        <button type="button" x-show="satker_id && !isSatkerLocked"
                            @click="satker_id = null; searchSatker = '';"
                            class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <flux:icon name="x-mark"
                                class="w-5 h-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" />
                        </button>

                        <div x-show="open && !isSatkerLocked" x-transition
                            class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 rounded-md shadow-lg max-h-60 overflow-auto">
                            <ul>
                                <template
                                    x-for="satker in satkerOptions.filter(s => s.nama_satker.toLowerCase().includes(searchSatker.toLowerCase()))"
                                    :key="satker.id">
                                    <li @click="selectSatker(satker.id, satker.nama_satker); open = false"
                                        class="px-4 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-gray-700">
                                        <span x-text="satker.nama_satker"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    @error('satker_id') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Input lain menggunakan x-model --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:input x-model="nama" :label="__('Nama Lengkap *')" />
                        @error('nama') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <flux:input x-model="usia" :label="__('Usia *')" type="number" />
                        @error('usia') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Kelamin *</label>
                    <div class="mt-2 flex gap-x-6">
                        <label class="inline-flex items-center">
                            <input type="radio" x-model="jenis_kelamin" value="Laki-laki"
                                class="form-radio text-indigo-600">
                            <span class="ml-2">Laki-laki</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" x-model="jenis_kelamin" value="Perempuan"
                                class="form-radio text-indigo-600">
                            <span class="ml-2">Perempuan</span>
                        </label>
                    </div>
                    @error('jenis_kelamin') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Dropdown Agama (Client-side Search) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agama *</label>
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative mt-1">
                        <input type="text" x-model="searchAgama" @focus="open = true" @input="agama = ''"
                            placeholder="Pilih atau ketik agama..." :readonly="agama"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            autocomplete="off">
                        <button type="button" x-show="agama" @click="agama = ''; searchAgama = '';"
                            class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <flux:icon name="x-mark" class="w-5 h-5 text-gray-400 hover:text-gray-600" />
                        </button>
                        <div x-show="open" x-transition
                            class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 rounded-md shadow-lg max-h-60 overflow-auto">
                            <ul>
                                <template
                                    x-for="option in agamaOptions.filter(o => o.toLowerCase().includes(searchAgama.toLowerCase()))"
                                    :key="option">
                                    <li @click="agama = option; searchAgama = option; open = false"
                                        class="px-4 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-gray-700">
                                        <span x-text="option"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    @error('agama') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Dropdown Pendidikan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pendidikan Terakhir
                        *</label>
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative mt-1">
                        <input type="text" x-model="searchPendidikan" @focus="open = true" @input="pendidikan = ''"
                            placeholder="Pilih atau ketik pendidikan..." :readonly="pendidikan"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            autocomplete="off">
                        <button type="button" x-show="pendidikan" @click="pendidikan = ''; searchPendidikan = '';"
                            class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <flux:icon name="x-mark" class="w-5 h-5 text-gray-400 hover:text-gray-600" />
                        </button>
                        <div x-show="open" x-transition
                            class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 rounded-md shadow-lg max-h-60 overflow-auto">
                            <ul>
                                <template
                                    x-for="option in pendidikanOptions.filter(o => o.toLowerCase().includes(searchPendidikan.toLowerCase()))"
                                    :key="option">
                                    <li @click="pendidikan = option; searchPendidikan = option; open = false"
                                        class="px-4 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-gray-700">
                                        <span x-text="option"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    @error('pendidikan') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Dropdown Pekerjaan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pekerjaan Utama *</label>
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative mt-1">
                        <input type="text" x-model="searchPekerjaan" @focus="open = true" @input="pekerjaan = ''"
                            placeholder="Pilih atau ketik pekerjaan..." :readonly="pekerjaan"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            autocomplete="off">
                        <button type="button" x-show="pekerjaan"
                            @click="pekerjaan = ''; searchPekerjaan = ''; pekerjaan_lainnya = ''"
                            class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <flux:icon name="x-mark" class="w-5 h-5 text-gray-400 hover:text-gray-600" />
                        </button>
                        <div x-show="open" x-transition
                            class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 rounded-md shadow-lg max-h-60 overflow-auto">
                            <ul>
                                <template
                                    x-for="option in pekerjaanOptions.filter(o => o.toLowerCase().includes(searchPekerjaan.toLowerCase()))"
                                    :key="option">
                                    <li @click="pekerjaan = option; searchPekerjaan = option; open = false"
                                        class="px-4 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-gray-700">
                                        <span x-text="option"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    @error('pekerjaan') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                <div x-show="pekerjaan === 'Lainnya'" x-transition class="animate-fade-in">
                    <flux:input x-model="pekerjaan_lainnya" label="Sebutkan Pekerjaan Anda *" />
                    @error('pekerjaan_lainnya') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input x-model="email" :label="__('Email (Opsional)')" type="email" />
                    @error('email') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:textarea x-model="alamat_lengkap" :label="__('Alamat Lengkap *')" rows="3" />
                    @error('alamat_lengkap') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:textarea x-model="keterangan_keperluan" :label="__('Keterangan Keperluan Layanan *')"
                        rows="3" />
                    @error('keterangan_keperluan') <span class="text-sm text-red-500 mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="mt-8 text-center">
                <flux:button @click="submitStep0()" variant="primary" x-bind:disabled="isLoading">{{ __('Lanjutkan &
                    Mulai Survei') }}</flux:button>
            </div>
        </div>
        @endif

        {{-- Langkah 1: Isi Kuesioner --}}
        @if ($langkah === 1)
        <div x-init="$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))"
            class="relative p-8 bg-white rounded-xl shadow-lg dark:bg-gray-800 animate-fade-in-up">
            <div class="absolute top-4 left-4">
                <flux:button wire:click="kembali" variant="ghost" icon="arrow-left">{{ __('Kembali') }}</flux:button>
            </div>

            <flux:heading size="xl" class="mb-6 text-center pt-8">
                {{ \App\Models\Satker::find($satker_id)?->nama_satker }}
            </flux:heading>
            <div class="space-y-10">
                @foreach($initialData['kuesioners'] as $kuesioner)
                <div id="question-{{ $kuesioner['id'] }}" @class([ 'p-6 rounded-lg transition-all duration-300'
                    , 'question-box' , 'invalid'=> $errors->has('jawaban.' . $kuesioner['id']) ])>
                    <flux:text class="font-semibold text-lg dark:text-gray-200">{{ $kuesioner['urutan'] }}. {{
                        $kuesioner['pertanyaan'] }}</flux:text>
                    <div class="mt-4 flex items-stretch gap-2 sm:gap-3"
                        style="--item-count: {{ count($kuesioner['pilihan_jawaban']) }};">
                        @foreach($kuesioner['pilihan_jawaban'] as $pilihan)
                        @php
                        $totalOptions = count($kuesioner['pilihan_jawaban']);
                        $style = $this->getStyleForValue($pilihan['nilai'], $totalOptions);
                        @endphp
                        <label
                            class="flex flex-1 flex-col items-center justify-center p-2 sm:p-3 border rounded-lg cursor-pointer transition-all duration-200 space-y-2"
                            :class="{
                                'shadow-md scale-105': answers[{{ $kuesioner['id'] }}] == {{ $pilihan['nilai'] }},
                                'bg-white text-gray-700 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600': answers[{{ $kuesioner['id'] }}] != {{ $pilihan['nilai'] }},
                                'bg-red-600 border-red-600 text-white': answers[{{ $kuesioner['id'] }}] == {{ $pilihan['nilai'] }} && '{{ $style['color'] }}' === 'red-600',
                                'bg-amber-500 border-amber-500 text-white': answers[{{ $kuesioner['id'] }}] == {{ $pilihan['nilai'] }} && '{{ $style['color'] }}' === 'amber-500',
                                'bg-sky-500 border-sky-500 text-white': answers[{{ $kuesioner['id'] }}] == {{ $pilihan['nilai'] }} && '{{ $style['color'] }}' === 'sky-500',
                                'bg-teal-500 border-teal-500 text-white': answers[{{ $kuesioner['id'] }}] == {{ $pilihan['nilai'] }} && '{{ $style['color'] }}' === 'teal-500',
                                'bg-green-500 border-green-500 text-white': answers[{{ $kuesioner['id'] }}] == {{ $pilihan['nilai'] }} && '{{ $style['color'] }}' === 'green-500',
                                'bg-emerald-500 border-emerald-500 text-white': answers[{{ $kuesioner['id'] }}] == {{ $pilihan['nilai'] }} && '{{ $style['color'] }}' === 'emerald-500'
                            }">
                            <input type="radio" x-model="answers[{{ $kuesioner['id'] }}]"
                                value="{{ $pilihan['nilai'] }}" class="sr-only">
                            @if (Str::startsWith($style['icon'], 'icons.'))
                            <x-dynamic-component :component="$style['icon']" class="w-7 h-7 sm:w-8 sm:h-8" />
                            @else
                            <flux:icon name="{{ $style['icon'] }}" class="w-7 h-7 sm:w-8 sm:h-8" />
                            @endif
                            <span
                                class="font-medium text-center break-words leading-tight text-[clamp(0.55rem,calc(12vw/var(--item-count)),0.875rem)]">
                                {{ $pilihan['label'] }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @error('jawaban.*') <span
                class="block w-full p-3 mt-6 text-center text-sm text-red-700 bg-red-100 rounded-lg">{{ $message
                }}</span> @enderror
            <div class="mt-10 text-center">
                <flux:button @click="submitStep1()" variant="primary" x-bind:disabled="isLoading">{{ __('Lanjutkan') }}
                </flux:button>
            </div>
        </div>
        @endif

        {{-- Langkah 2: Isi Kritik & Saran --}}
        @if ($langkah === 2)
        <div class="relative p-8 bg-white rounded-xl shadow-lg dark:bg-gray-800 animate-fade-in-up">
            <div class="absolute top-4 left-4">
                <flux:button wire:click="kembali" variant="ghost" icon="arrow-left">{{ __('Kembali') }}</flux:button>
            </div>
            <div class="text-center pt-8">
                <flux:icon name="chat-bubble-left-ellipsis" class="w-16 h-16 mx-auto text-indigo-500" />
                <flux:heading size="xl" class="mt-4">{{ __('Satu Langkah Terakhir') }}</flux:heading>
                <flux:text size="lg" class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ __('Apakah Anda memiliki kritik atau saran untuk perbaikan layanan kami di masa mendatang?
                    (Opsional)') }}
                </flux:text>
            </div>
            <div class="mt-8">
                <flux:textarea x-model="kritik_saran" placeholder="Tuliskan masukan Anda di sini..." rows="5" />
                @error('kritik_saran') <span class="text-sm text-red-500 mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="flex justify-center items-center mt-8 gap-4">
                <flux:button @click="kritik_saran = ''; submitStep2()" variant="ghost" x-bind:disabled="isLoading">
                    {{ __('Lewati') }}
                </flux:button>
                <flux:button @click="submitStep2()" variant="primary" x-bind:disabled="isLoading">
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
                {{ __('Jawaban survei Anda telah berhasil kami terima. Partisipasi Anda sangat berarti untuk
                meningkatkan kualitas layanan kami.') }}
            </flux:text>
            <div class="mt-8">
                <flux:button wire:click="surveyLagi" variant="ghost">{{ __('Isi Survei Lagi') }}</flux:button>
            </div>
        </div>
        @endif
        <script>
            function getSurveyInitialData() {
                return @js($initialData);
            }
        </script>
    </div>
</div>
