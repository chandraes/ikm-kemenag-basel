<?php

namespace App\Livewire\Survey;

use App\Models\JawabanItem;
use App\Models\JawabanSurvey;
use App\Models\Kuesioner;
use App\Models\Satker;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Session;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Illuminate\Validation\ValidationException;
use App\Events\SurveySubmitted;

class FormSurvey extends Component
{
    // Properti untuk data
    public $satkers;
    public $kuesioners;
    public string $searchSatker = '';
    public string $searchAgama = '';
    public string $searchPendidikan = '';
    public string $searchPekerjaan = '';
    // Properti untuk input pengguna
    #[Session]
    public $satker_id;
    #[Session]
    public array $jawaban = [];
    #[Session]
    public $nama;
    #[Session]
    public $email;
    #[Session]
    public $usia;
    #[Session]
    public $alamat_lengkap;
    #[Session]
    public $keterangan_keperluan;
    #[Session]
    public $kritik_saran;

      // =======================================================
    // TAMBAHKAN: Properti baru dengan #[Session]
    // =======================================================
    #[Session]
    public $jenis_kelamin;
    #[Session]
    public $agama;
    #[Session]
    public $pendidikan;
    #[Session]
    public $pekerjaan;
    #[Session]
    public $pekerjaan_lainnya;

    // TAMBAHKAN: Opsi untuk dropdown
    public $agamaOptions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Khonghucu'];
    public $pendidikanOptions = ['SD', 'SMP', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'];
    public $pekerjaanOptions = ['PNS/ASN', 'TNI/POLRI', 'Wiraswasta/Wirausaha', 'Pelajar/Mahasiswa', 'Honorer', 'Petani', 'Nelayan', 'Ibu Rumah Tangga', 'Lainnya'];

    // Properti untuk mengontrol tampilan
    #[Session]
    public int $langkah = 0;
    public $totalKuesioner;


    public function mount()
    {
        $this->satkers = Satker::orderBy('nama_satker')->get();
        $this->kuesioners = Kuesioner::orderBy('urutan')->get();
        $this->totalKuesioner = $this->kuesioners->count();

        // Inisialisasi array jawaban hanya jika belum ada di sesi
        if (empty($this->jawaban)) {
            foreach ($this->kuesioners as $kuesioner) {
                $this->jawaban[$kuesioner->id] = null;
            }
        }
    }

    public function selectSatker($id, $nama)
    {
        $this->satker_id = $id;
        $this->searchSatker = $nama; // Isi input dengan nama yang dipilih
    }

    // Method baru untuk mereset pilihan
    public function resetSatker()
    {
        $this->reset('satker_id', 'searchSatker');
    }

     public function selectAgama($agama)
    {
        $this->agama = $agama;
        $this->searchAgama = $agama;
    }

    public function resetAgama()
    {
        $this->reset('agama', 'searchAgama');
    }

    public function selectPendidikan($pendidikan)
    {
        $this->pendidikan = $pendidikan;
        $this->searchPendidikan = $pendidikan;
    }

    public function resetPendidikan()
    {
        $this->reset('pendidikan', 'searchPendidikan');
    }

    public function selectPekerjaan($pekerjaan)
    {
        $this->pekerjaan = $pekerjaan;
        $this->searchPekerjaan = $pekerjaan;
    }

    public function resetPekerjaan()
    {
        $this->reset('pekerjaan', 'searchPekerjaan', 'pekerjaan_lainnya');
    }

    public function mulaiSurvey()
    {
        $this->validate([
            'satker_id' => 'required|exists:satkers,id',
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'usia' => 'required|integer|min:15|max:100',
            'alamat_lengkap' => 'required|string|min:10',
            'keterangan_keperluan' => 'required|string|min:5',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|in:' . implode(',', $this->agamaOptions),
            'pendidikan' => 'required|in:' . implode(',', $this->pendidikanOptions),
            'pekerjaan' => 'required|in:' . implode(',', $this->pekerjaanOptions),
            'pekerjaan_lainnya' => 'required_if:pekerjaan,Lainnya|nullable|string|max:255',
        ]);

        $this->langkah = 1;
    }

    public function getStyleForValue(int $currentValue, int $totalOptions): array
    {
        $colors = ['red-600', 'amber-500', 'sky-500', 'teal-500', 'green-500', 'emerald-500'];
        $icons = [
            'face-frown',
            'icons.face-neutral',
            'face-smile',
            'hand-thumb-up',
            'star',
            'sparkles',
        ];

        if ($totalOptions <= 1) {
            return ['icon' => 'question-mark-circle', 'color' => 'gray-500'];
        }

        if ($currentValue === 1) {
            return ['icon' => $icons[0], 'color' => $colors[0]];
        }

        if ($currentValue === $totalOptions) {
            return ['icon' => end($icons), 'color' => end($colors)];
        }

        $middleIndexColor = ($currentValue - 1) % (count($colors) - 1);
        $middleIndexIcon = ($currentValue - 1) % (count($icons) - 1);

        return [
            'icon' => $icons[$middleIndexIcon] ?? end($icons),
            'color' => $colors[$middleIndexColor] ?? end($colors)
        ];
    }

    public function simpanSurvey()
    {
        $rules = [];
        foreach ($this->kuesioners as $kuesioner) {
            $rules['jawaban.' . $kuesioner->id] = 'required';
        }

        try {
            $this->validate($rules, ['jawaban.*.required' => 'Mohon jawab semua pertanyaan sebelum melanjutkan.']);
            $this->langkah = 2;

        } catch (ValidationException $e) {
            $firstErrorKey = array_key_first($e->errors());
            $kuesionerId = str_replace('jawaban.', '', $firstErrorKey);

            $this->dispatch('focus-on-question', elementId: 'question-' . $kuesionerId);

            LivewireAlert::title('Ada Pertanyaan Terlewat')
                ->text('Mohon isi semua pertanyaan untuk melanjutkan.')
                ->error()
                ->show();
        }
    }

    public function finalkanSurvey()
    {
        $this->validate(['kritik_saran' => 'nullable|string']);

        try {
            DB::transaction(function () {
                $jawabanSurvey = JawabanSurvey::create([
                    'satker_id' => $this->satker_id,
                    'nama' => $this->nama,
                    'email' => $this->email,
                    'usia' => $this->usia,
                    'alamat_lengkap' => $this->alamat_lengkap,
                    'keterangan_keperluan' => $this->keterangan_keperluan,
                    'kritik_saran' => $this->kritik_saran,
                    // =======================================================
                    // TAMBAHKAN: Menyimpan data demografi baru
                    // =======================================================
                    'jenis_kelamin' => $this->jenis_kelamin,
                    'agama' => $this->agama,
                    'pendidikan' => $this->pendidikan,
                    'pekerjaan' => $this->pekerjaan,
                    'pekerjaan_lainnya' => $this->pekerjaan === 'Lainnya' ? $this->pekerjaan_lainnya : null,
                ]);

                foreach ($this->jawaban as $kuesionerId => $nilaiJawaban) {
                    $kuesioner = $this->kuesioners->find($kuesionerId);

                    JawabanItem::create([
                        'jawaban_survey_id' => $jawabanSurvey->id,
                        'kuesioner_id' => $kuesionerId,
                        'satker_id' => $this->satker_id,
                        'jawaban_nilai' => (int) $nilaiJawaban,
                        'jawaban_label' => $kuesioner->pilihan_jawaban[(int) $nilaiJawaban - 1]['label'] ?? 'Tidak valid',
                        'created_at' => $jawabanSurvey->created_at,
                        'updated_at' => $jawabanSurvey->created_at,
                    ]);
                }
            });

            $this->langkah = 3;

            broadcast(new SurveySubmitted())->toOthers();

        } catch (\Exception $e) {
            LivewireAlert::title('Terjadi Kesalahan')->text('Gagal menyimpan survei, silakan coba lagi.')->error()->show();
        }
    }

    public function surveyLagi()
    {
        session()->forget([
            'satker_id', 'jawaban', 'nama', 'email', 'usia', 'alamat_lengkap',
            'keterangan_keperluan', 'kritik_saran', 'langkah', 'jenis_kelamin',
            'agama', 'pendidikan', 'pekerjaan', 'pekerjaan_lainnya'
        ]);

        $this->reset();
        $this->mount();
    }

    public function kembali()
    {
        if ($this->langkah > 0) {
            $this->langkah--;
        }
    }

   public function render()
    {
         $satkers = Satker::query()
            ->when($this->searchSatker, function ($query) {
                $query->where('nama_satker', 'like', '%' . $this->searchSatker . '%');
            })
            ->orderBy('nama_satker')
            ->take(10)
            ->get();

        $filteredAgamaOptions = collect($this->agamaOptions)->filter(function ($option) {
            return stristr($option, $this->searchAgama);
        });

        $filteredPendidikanOptions = collect($this->pendidikanOptions)->filter(function ($option) {
            return stristr($option, $this->searchPendidikan);
        });

        $filteredPekerjaanOptions = collect($this->pekerjaanOptions)->filter(function ($option) {
            return stristr($option, $this->searchPekerjaan);
        });

        if ($this->satker_id && empty($this->searchSatker)) {
            $selectedSatker = Satker::find($this->satker_id);
            if($selectedSatker) {
                $this->searchSatker = $selectedSatker->nama_satker;
            }
        }

        if ($this->agama && empty($this->searchAgama)) { $this->searchAgama = $this->agama; }
        if ($this->pendidikan && empty($this->searchPendidikan)) { $this->searchPendidikan = $this->pendidikan; }
        if ($this->pekerjaan && empty($this->searchPekerjaan)) { $this->searchPekerjaan = $this->pekerjaan; }

        return view('livewire.survey.form-survey', [
            'filteredSatkers' => $satkers,
            'filteredAgamaOptions' => $filteredAgamaOptions,
            'filteredPendidikanOptions' => $filteredPendidikanOptions,
            'filteredPekerjaanOptions' => $filteredPekerjaanOptions,
        ])->layout('components.layouts.guest');
    }
}
