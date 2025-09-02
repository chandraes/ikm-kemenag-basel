<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\JawabanItem;
use App\Models\JawabanSurvey;
use App\Models\Satker;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Features\SupportJs\JS;

class Index extends Component
{
    public string $periode = 'all';
    public bool $isShareModalOpen = false;

    #[On('echo:dashboard,SurveySubmitted')]
    public function refreshDashboard()
    {
        // Method ini hanya untuk memicu re-render saat ada event.
        // Logika utama ada di dalam method render().
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

    public function openShareModal()
    {
        $this->isShareModalOpen = true;

        // Siapkan URL global
        $globalUrl = route('survey.form');

        // Langsung panggil fungsi JS untuk membuat QR Code
        return $this->js("generateQrCode('" . $globalUrl . "')");
    }

    public function closeShareModal()
    {
        $this->isShareModalOpen = false;
    }

    public function render()
    {
        // Hitung data yang hanya dibutuhkan oleh komponen ini (statistik dan chart)
        $totalResponden = JawabanSurvey::query()
            ->when($this->periode !== 'all', function ($query) {
                return $query->where('created_at', '>=', $this->getStartDate());
            })
            ->count();

        $itemsQuery = JawabanItem::query()
            ->when($this->periode !== 'all', function ($query) {
                return $query->where('created_at', '>=', $this->getStartDate());
            });

        $rataRataNasional = (clone $itemsQuery)->avg('jawaban_nilai');
        $ikmNasional = $rataRataNasional ? number_format($rataRataNasional * 25, 2) : "0.00";
        $ikmScoreForChart = $rataRataNasional ? (float)number_format($rataRataNasional * 25, 2, '.', '') : 0.0;
        $mutuPelayananInstansi = getIkmGrade($ikmScoreForChart);

        $ulasanTerbaru = JawabanSurvey::with('satker')
            ->whereNotNull('kritik_saran')
            ->where('kritik_saran', '!=', '')
            ->latest()
            ->take(5)
            ->get();

        // Query data mentah untuk chart
        $hasilPerSatker = Satker::query()
            ->withAvg(['jawabanItems' => function ($query) {
                $query->when($this->periode !== 'all', function ($q) {
                    $q->where('created_at', '>=', $this->getStartDate());
                });
            }], 'jawaban_nilai')
            ->get();

        // Urutkan data khusus untuk tampilan visual bar chart
        $sortedForChart = $hasilPerSatker->sortByDesc('jawaban_items_avg_jawaban_nilai');

        $dataGauge = ['series' => [$ikmScoreForChart]];
        $dataBarChart = [
            'series' => [['name' => 'Skor IKM', 'data' => $sortedForChart->pluck('jawaban_items_avg_jawaban_nilai')->map(fn($val) => $val ? (float)number_format($val * 25, 2, '.', '') : 0)->toArray()]],
            'categories' => $sortedForChart->pluck('nama_satker')->toArray()
        ];

        return view('livewire.admin.dashboard.index', [
            'totalResponden' => $totalResponden,
            'ikmNasional' => $ikmNasional,
            'mutuPelayananInstansi' => $mutuPelayananInstansi,
            'ulasanTerbaru' => $ulasanTerbaru,
            'dataGauge' => $dataGauge,
            'dataBarChart' => $dataBarChart,
        ]);
    }
}
