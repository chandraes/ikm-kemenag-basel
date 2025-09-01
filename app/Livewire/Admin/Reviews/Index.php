<?php

namespace App\Livewire\Admin\Reviews;

use App\Models\JawabanSurvey;
use App\Models\Satker;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Features\SupportJs\JS;

class Index extends Component
{
    use WithPagination;

    // Properti untuk filter
    public string $search = '';
    public $filterSatker = '';
    public string $filterWaktu = 'all';

    // Properti untuk UI
    public $satkers;

    public function mount()
    {
        // Ambil data satker untuk opsi filter
        $this->satkers = Satker::orderBy('nama_satker')->get();
    }

    // Reset halaman ke 1 setiap kali ada filter baru
    public function updating($key)
    {
        if (in_array($key, ['search', 'filterSatker', 'filterWaktu'])) {
            $this->resetPage();
        }
    }

    // Fungsi untuk mereset semua filter
    public function resetFilters()
    {
        $this->reset(['search', 'filterSatker', 'filterWaktu']);
    }

    // Helper untuk menentukan tanggal mulai berdasarkan filter waktu
    protected function getStartDate()
    {
        return match ($this->filterWaktu) {
            'this_month' => now()->startOfMonth(),
            'last_2_months' => now()->subMonths(2)->startOfMonth(),
            'this_year' => now()->startOfYear(),
            default => null,
        };
    }

    public function exportPdfData()
    {
        // 1. Jalankan query yang sama persis dengan render(), tapi tanpa paginasi
        $query = JawabanSurvey::with('satker')
            ->whereNotNull('kritik_saran')
            ->where('kritik_saran', '!=', '');

        // Terapkan semua filter yang sedang aktif
        $query->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                    ->orWhere('kritik_saran', 'like', '%' . $this->search . '%');
            });
        })
            ->when($this->filterSatker, fn($q) => $q->where('satker_id', $this->filterSatker))
            ->when($this->filterWaktu !== 'all', function ($q) {
                $q->where('created_at', '>=', $this->getStartDate());
            });

        // 2. Ambil semua data yang cocok dengan menggunakan get()
        $reviewsToExport = $query->latest()->get();

        // 3. Kembalikan perintah JavaScript ke browser untuk memanggil fungsi generatePdf()
        // Data dikirim setelah di-encode dengan JSON lalu Base64 agar aman
        return $this->js("generatePdf(JSON.parse(atob('" . base64_encode(json_encode($reviewsToExport)) . "')))");
    }

    public function render()
    {
        // Ambil data survei yang memiliki kritik dan saran
        $query = JawabanSurvey::with('satker')
            ->whereNotNull('kritik_saran')
            ->where('kritik_saran', '!=', '');

        // Terapkan filter
        $query->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                    ->orWhere('kritik_saran', 'like', '%' . $this->search . '%');
            });
        })
            ->when($this->filterSatker, fn($q) => $q->where('satker_id', $this->filterSatker))
            ->when($this->filterWaktu !== 'all', function ($q) {
                $q->where('created_at', '>=', $this->getStartDate());
            });

        $reviews = $query->latest()->paginate(10);

        return view('livewire.admin.reviews.index', [
            'reviews' => $reviews,
        ]);
    }
}
