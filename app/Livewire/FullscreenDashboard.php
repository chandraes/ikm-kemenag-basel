<?php

namespace App\Livewire;

use App\Models\JawabanItem;
use App\Models\JawabanSurvey;
use App\Models\Satker;
use Livewire\Component;
use Livewire\Attributes\On;

class FullscreenDashboard extends Component
{
    public string $periode = 'all';
    public string $sortBy = 'skor_ikm';
    public string $sortDirection = 'desc';

    #[On('echo:dashboard,SurveySubmitted')]
    public function refreshDashboard() { }

    public function setSortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortBy = $field;
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
        // Semua logika pengambilan data disatukan di sini
        $totalResponden = JawabanSurvey::query()
            ->when($this->periode !== 'all', function ($query) {
                $query->where('created_at', '>=', $this->getStartDate());
            })->count();

        $itemsQuery = JawabanItem::query()
            ->when($this->periode !== 'all', function ($query) {
                $query->where('created_at', '>=', $this->getStartDate());
            });

        $rataRataNasional = (clone $itemsQuery)->avg('jawaban_nilai');
        $ikmNasional = $rataRataNasional ? number_format($rataRataNasional * 25, 2) : "0.00";
        $ikmScoreForChart = $rataRataNasional ? (float)number_format($rataRataNasional * 25, 2, '.', '') : 0.0;
        $mutuPelayananInstansi = getIkmGrade($ikmScoreForChart);

        $hasilPerSatkerCollection = Satker::query()
            ->withCount(['jawabanSurveys' => function ($query) {
                $query->when($this->periode !== 'all', function ($q) {
                    $q->where('created_at', '>=', $this->getStartDate());
                });
            }])
            ->withAvg(['jawabanItems' => function ($query) {
                $query->when($this->periode !== 'all', function ($q) {
                    $q->where('created_at', '>=', $this->getStartDate());
                });
            }], 'jawaban_nilai')
            ->get()
            ->map(function ($satker) {
                $skorIkm = $satker->jawaban_items_avg_jawaban_nilai ? (float)number_format($satker->jawaban_items_avg_jawaban_nilai * 25, 2, '.', '') : 0.0;
                $satker->skor_ikm = $skorIkm;
                $satker->mutu = getIkmGrade($skorIkm);
                $satker->mutu_grade = $satker->mutu['grade'];
                return $satker;
            });

        if ($this->sortDirection === 'asc') {
            $hasilPerSatkerTersortir = $hasilPerSatkerCollection->sortBy($this->sortBy);
        } else {
            $hasilPerSatkerTersortir = $hasilPerSatkerCollection->sortByDesc($this->sortBy);
        }

        $sortedForChart = $hasilPerSatkerCollection->sortByDesc('skor_ikm');
        $dataGauge = ['series' => [$ikmScoreForChart]];
        $dataBarChart = [
            'series' => [['name' => 'Skor IKM', 'data' => $sortedForChart->pluck('skor_ikm')->toArray()]],
            'categories' => $sortedForChart->pluck('nama_satker')->toArray()
        ];

         $ulasanTerbaru = JawabanSurvey::with('satker')
            ->whereNotNull('kritik_saran')
            ->where('kritik_saran', '!=', '')
            ->latest()
            ->take(15)
            ->get();

        $this->dispatch('chart-data-updated', barData: $dataBarChart, gaugeData: $dataGauge);

        return view('livewire.fullscreen-dashboard', [
            'totalResponden' => $totalResponden,
            'ikmNasional' => $ikmNasional,
            'mutuPelayananInstansi' => $mutuPelayananInstansi,
            'hasilPerSatker' => $hasilPerSatkerTersortir,
            'dataGauge' => $dataGauge,
            'ulasanTerbaru' => $ulasanTerbaru,
            'dataBarChart' => $dataBarChart,
        ])->layout('components.layouts.dashboard'); // Gunakan layout baru
    }
}
