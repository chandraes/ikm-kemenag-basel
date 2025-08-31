<?php

namespace App\Livewire;

use App\Models\JawabanItem;
use App\Models\JawabanSurvey;
use App\Models\Satker;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LandingPage extends Component
{
    // Properti untuk filter
    public string $periode = 'all'; // Opsi: '30d', '90d', '1y', 'all'

    // Properti untuk menampung data hasil
    public $totalResponden;
    public $ikmNasional;
    public $hasilPerSatker;
    public $ulasanTerbaru;

    public function mount()
    {
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

        // 3. Hitung IKM Nasional
        $rataRataNasional = $itemsQuery->avg('jawaban_nilai');
        $this->ikmNasional = $rataRataNasional ? number_format($rataRataNasional * 25, 2) : 0;

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
                $satker->skor_ikm = $satker->jawaban_items_avg_jawaban_nilai ? number_format($satker->jawaban_items_avg_jawaban_nilai * 25, 2) : 0;
                return $satker;
            });

        // 5. Ambil Ulasan Terbaru
        $this->ulasanTerbaru = JawabanSurvey::whereNotNull('kritik_saran')
            ->where('kritik_saran', '!=', '')
            ->latest()
            ->take(5)
            ->get();
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
