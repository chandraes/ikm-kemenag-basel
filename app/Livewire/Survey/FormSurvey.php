<?php

namespace App\Livewire\Survey;

use App\Events\SurveySubmitted;
use App\Models\JawabanItem;
use App\Models\JawabanSurvey;
use App\Models\Kuesioner;
use App\Models\Satker;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;

class FormSurvey extends Component
{
    // Properti ini diisi oleh Alpine saat submit
    public $satker_id, $jawaban = [], $nama, $email, $usia, $alamat_lengkap, $keterangan_keperluan, $kritik_saran, $jenis_kelamin, $agama, $pendidikan, $pekerjaan, $pekerjaan_lainnya;

    // Properti untuk state
    public int $langkah = 0;
    public $satker; // dari route
    public bool $isSatkerLocked = false;

    public function mount()
    {
        if ($this->satker) {
            $selectedSatker = Satker::find($this->satker);
            if ($selectedSatker) {
                $this->satker_id = $selectedSatker->id;
                $this->isSatkerLocked = true;
            }
        }
    }

    public function mulaiSurvey($formData)
    {
        $this->fill($formData);

        $agamaOptions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Khonghucu'];
        $pendidikanOptions = ['SD', 'SMP', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'];
        $pekerjaanOptions = ['PNS/ASN', 'TNI/POLRI', 'Wiraswasta/Wirausaha', 'Pelajar/Mahasiswa', 'Honorer', 'Petani', 'Nelayan', 'Ibu Rumah Tangga', 'Lainnya'];

        $this->validate([
            'satker_id' => 'required|exists:satkers,id',
            'nama' => 'required|string|max:255',
            'usia' => 'required|integer|min:15|max:100',
            'alamat_lengkap' => 'required|string|min:10',
            'keterangan_keperluan' => 'required|string|min:5',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|in:' . implode(',', $agamaOptions),
            'pendidikan' => 'required|in:' . implode(',', $pendidikanOptions),
            'pekerjaan' => 'required|in:' . implode(',', $pekerjaanOptions),
            'pekerjaan_lainnya' => 'required_if:pekerjaan,Lainnya|nullable|string|max:255',
        ]);
        $this->langkah = 1;
    }

    public function simpanSurvey($formData)
    {
        $this->fill($formData);
        $rules = [];
        $kuesioners = Kuesioner::all();
        foreach ($kuesioners as $kuesioner) {
            $rules['jawaban.' . $kuesioner->id] = 'required';
        }

        try {
            $this->validate($rules, ['jawaban.*.required' => 'Mohon jawab semua pertanyaan.']);
            $this->langkah = 2;
        } catch (ValidationException $e) {
            $this->dispatch('focus-on-question', elementId: 'question-' . str_replace('jawaban.', '', array_key_first($e->errors())));
            LivewireAlert::error('Ada Pertanyaan Terlewat', 'Mohon isi semua pertanyaan untuk melanjutkan.');
        }
    }

    public function finalkanSurvey($formData)
    {
        $this->fill($formData);
        $this->validate(['kritik_saran' => 'nullable|string']);
        try {
            DB::transaction(function () {
                $jawabanSurvey = JawabanSurvey::create($this->only(['satker_id', 'nama', 'email', 'usia', 'alamat_lengkap', 'keterangan_keperluan', 'kritik_saran', 'jenis_kelamin', 'agama', 'pendidikan', 'pekerjaan', 'pekerjaan_lainnya']));
                foreach ($this->jawaban as $kuesionerId => $nilaiJawaban) {
                    $kuesioner = Kuesioner::find($kuesionerId);
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
            LivewireAlert::error('Terjadi Kesalahan', 'Gagal menyimpan survei: ' . $e->getMessage());
        }
    }

    public function surveyLagi()
    {
        return redirect()->to(route('survey.form'));
    }

    public function kembali()
    {
        if ($this->langkah > 0) {
            $this->langkah--;
        }
    }

    public function getStyleForValue(int $currentValue, int $totalOptions): array
    {
        $colors = ['red-600', 'amber-500', 'sky-500', 'teal-500', 'green-500', 'emerald-500'];
        $icons = ['face-frown', 'face-smile', 'hand-thumb-up', 'star', 'sparkles'];
        if ($totalOptions <= 1) return ['icon' => 'question-mark-circle', 'color' => 'gray-500'];
        if ($currentValue === 1) return ['icon' => $icons[0], 'color' => $colors[0]];
        if ($currentValue === $totalOptions) return ['icon' => end($icons), 'color' => end($colors)];
        $middleIndexColor = ($currentValue - 1) % (count($colors) - 1);
        $middleIndexIcon = ($currentValue - 1) % (count($icons) - 1);
        return ['icon' => $icons[$middleIndexIcon] ?? end($icons), 'color' => $colors[$middleIndexColor] ?? end($colors)];
    }

    public function render()
    {
        $initialData = [
            'satker_id' => $this->satker_id,
            'isSatkerLocked' => $this->isSatkerLocked,
            'searchSatker' => $this->isSatkerLocked ? Satker::find($this->satker_id)?->nama_satker : '',
            'satkerOptions' => Satker::orderBy('nama_satker')->get(['id', 'nama_satker']),
            'kuesioners' => Kuesioner::orderBy('urutan')->get(),
            'agamaOptions' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Khonghucu'],
            'pendidikanOptions' => ['SD', 'SMP', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'],
            'pekerjaanOptions' => ['PNS/ASN', 'TNI/POLRI', 'Wiraswasta/Wirausaha', 'Pelajar/Mahasiswa', 'Honorer', 'Petani', 'Nelayan', 'Ibu Rumah Tangga', 'Lainnya'],
        ];

        return view('livewire.survey.form-survey', [
            'initialData' => $initialData
        ])->layout('components.layouts.guest');
    }
}
