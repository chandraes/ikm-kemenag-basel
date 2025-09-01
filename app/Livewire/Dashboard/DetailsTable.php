<?php

namespace App\Livewire\Dashboard;

use App\Models\Satker;
use Livewire\Component;
use Livewire\Attributes\On;

class DetailsTable extends Component
{
    // Properti ini akan menerima data dari komponen induk (LandingPage)
    public string $periode = 'all';

    // Properti sorting sekarang tinggal di sini
    public string $sortBy = 'skor_ikm';
    public string $sortDirection = 'desc';

    // Listener event yang sama seperti di LandingPage
    #[On('echo:dashboard,SurveySubmitted')]
    public function refreshTable()
    {
        // Method ini hanya untuk memicu re-render komponen ini saja.
    }

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
        // Query untuk mengambil dan memproses data satker
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

        // Terapkan sorting pada data
        if ($this->sortDirection === 'asc') {
            $hasilPerSatkerTersortir = $hasilPerSatkerCollection->sortBy($this->sortBy);
        } else {
            $hasilPerSatkerTersortir = $hasilPerSatkerCollection->sortByDesc($this->sortBy);
        }

        return view('livewire.dashboard.details-table', [
            'hasilPerSatker' => $hasilPerSatkerTersortir,
        ]);
    }
}
