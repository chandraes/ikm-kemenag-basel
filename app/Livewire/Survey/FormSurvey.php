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

    public function mulaiSurvey()
    {
        $this->validate([
            'satker_id' => 'required|exists:satkers,id',
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'usia' => 'required|integer|min:15|max:100',
            'alamat_lengkap' => 'required|string|min:10',
            'keterangan_keperluan' => 'required|string|min:5',
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
        $this->reset();

        $this->langkah = 0;
        $this->satker_id = null;
        $this->nama = null;
        $this->email = null;
        $this->usia = null;
        $this->alamat_lengkap = null;
        $this->keterangan_keperluan = null;
        $this->kritik_saran = null;
        $this->jawaban = [];

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
        return view('livewire.survey.form-survey')->layout('components.layouts.guest');
    }
}
