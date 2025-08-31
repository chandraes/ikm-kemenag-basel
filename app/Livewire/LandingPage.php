<?php

namespace App\Livewire;

use App\Models\JawabanItem;
use App\Models\JawabanSurvey;
use App\Models\Satker;
use Livewire\Component;
use Livewire\Attributes\On; // <-- 1. Import atribut On
use App\Events\SurveySubmitted;

class LandingPage extends Component
{
    public string $periode = 'all';
    public $totalResponden;
    public $ikmNasional; // Ini akan tetap menjadi string untuk tampilan
    public $hasilPerSatker;
    public $ulasanTerbaru;

    public array $dataGauge = [];
    public array $dataBarChart = [];
    public $mutuPelayananInstansi;

    public function mount()
    {
        $this->hitungData();
    }

    #[On('echo:dashboard,SurveySubmitted')]
    public function refreshDashboard()
    {
        // Cukup panggil ulang method yang sudah ada untuk menghitung data terbaru
        $this->hitungData();
    }

    public function updatedPeriode()
    {
        $this->hitungData();
    }

    public function hitungData()
    {
        // 1. Hitung Total Responden berdasarkan periode
        $this->totalResponden = JawabanSurvey::query()
            ->when($this->periode !== 'all', function ($query) {
                return $query->where('created_at', '>=', $this->getStartDate());
            })
            ->count();

        // 2. Query dasar untuk item jawaban berdasarkan periode
        $itemsQuery = JawabanItem::query()
            ->when($this->periode !== 'all', function ($query) {
                return $query->where('created_at', '>=', $this->getStartDate());
            });

        // 3. Hitung IKM Instansi
        $rataRataNasional = (clone $itemsQuery)->avg('jawaban_nilai');

        // Versi string untuk kartu statistik
        $this->ikmNasional = $rataRataNasional ? number_format($rataRataNasional * 25, 2) : "0.00";

        // Versi ANGKA murni untuk grafik dan perhitungan mutu
        $ikmScoreForChart = $rataRataNasional ? (float)number_format($rataRataNasional * 25, 2, '.', '') : 0.0;

        // Hitung mutu pelayanan instansi menggunakan helper
        $this->mutuPelayananInstansi = getIkmGrade($ikmScoreForChart);

        // 4. Hitung IKM per Satker
        $this->hasilPerSatker = Satker::withCount(['jawabanItems' => function ($query) {
            $query->when($this->periode !== 'all', function ($q) {
                $q->where('created_at', '>=', $this->getStartDate());
            });
        }])
            ->withAvg(['jawabanItems' => function ($query) {
                $query->when($this->periode !== 'all', function ($q) {
                    $q->where('created_at', '>=', $this->getStartDate());
                });
            }], 'jawaban_nilai')
            ->orderBy('jawaban_items_avg_jawaban_nilai', 'desc')
            ->get()
            ->map(function ($satker) {
                // Pastikan skor untuk bar chart juga dalam format angka
                $skorIkm = $satker->jawaban_items_avg_jawaban_nilai ? (float)number_format($satker->jawaban_items_avg_jawaban_nilai * 25, 2, '.', '') : 0.0;
                $satker->skor_ikm = $skorIkm;

                // Tambahkan data mutu ke setiap satker menggunakan helper
                $satker->mutu = getIkmGrade($skorIkm);

                return $satker;
            });

        // 5. Ambil Ulasan Terbaru
        $this->ulasanTerbaru = JawabanSurvey::whereNotNull('kritik_saran')
            ->where('kritik_saran', '!=', '')
            ->latest()
            ->take(5)
            ->get();

        // 6. Siapkan data dalam format array sederhana untuk dikirim ke JS
        $this->dataGauge = [
            'series' => [$ikmScoreForChart]
        ];

        $this->dataBarChart = [
            'series' => [
                ['name' => 'Skor IKM', 'data' => $this->hasilPerSatker->pluck('skor_ikm')->toArray()]
            ],
            'categories' => $this->hasilPerSatker->pluck('nama_satker')->toArray()
        ];
    }

    protected function getStartDate()
    {
        return match ($this->periode) {
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.landing-page')->layout('components.layouts.guest');
    }
}
