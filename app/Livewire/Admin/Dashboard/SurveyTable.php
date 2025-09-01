<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\JawabanSurvey;
use App\Models\Satker;
use Livewire\Component;
use Livewire\WithPagination;

class SurveyTable extends Component
{
    use WithPagination;

    // Properti untuk filter & pencarian
    public string $search = '';
    public $satkerId;
    public $pendidikan;
    public $pekerjaan;
    public $startDate;
    public $endDate;

    public string $searchSatker = '';
    public string $searchPendidikan = '';
    public string $searchPekerjaan = '';

    // Properti untuk UI
    public int $perPage = 5;
    public $satkers;
    public $pendidikanOptions = ['SD', 'SMP', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'];
    public $pekerjaanOptions = ['PNS/ASN', 'TNI/POLRI', 'Wiraswasta/Wirausaha', 'Pelajar/Mahasiswa', 'Honorer', 'Petani', 'Nelayan', 'Ibu Rumah Tangga', 'Lainnya'];

    public function mount()
    {
        // Ambil data untuk opsi filter
        $this->satkers = Satker::orderBy('nama_satker')->get();
    }

    // Reset halaman ke 1 setiap kali ada filter baru
    public function updating($key)
    {
        if (in_array($key, ['search', 'satkerId', 'pendidikan', 'pekerjaan', 'startDate', 'endDate', 'perPage'])) {
            $this->resetPage();
        }
    }

    // Fungsi untuk mereset semua filter
    public function resetFilters()
    {
        $this->reset(['search', 'satkerId', 'pendidikan', 'pekerjaan', 'startDate', 'endDate', 'searchSatker', 'searchPendidikan', 'searchPekerjaan']);
    }

     public function selectSatker($id, $nama)
    {
        $this->satkerId = $id;
        $this->searchSatker = $nama;
    }

    public function selectPendidikan($pendidikan)
    {
        $this->pendidikan = $pendidikan;
        $this->searchPendidikan = $pendidikan;
    }

    public function selectPekerjaan($pekerjaan)
    {
        $this->pekerjaan = $pekerjaan;
        $this->searchPekerjaan = $pekerjaan;
    }

    public function render()
    {
         $satkers = Satker::query()
            ->when($this->searchSatker, function ($query) {
                $query->where('nama_satker', 'like', '%' . $this->searchSatker . '%');
            })
            ->orderBy('nama_satker')
            ->get();

        $filteredPendidikanOptions = collect($this->pendidikanOptions)->filter(function ($option) {
            return stristr($option, $this->searchPendidikan);
        });

        $filteredPekerjaanOptions = collect($this->pekerjaanOptions)->filter(function ($option) {
            return stristr($option, $this->searchPekerjaan);
        });

        $query = JawabanSurvey::with('satker')
            // Filter Pencarian: Nama atau Keperluan
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama', 'like', '%' . $this->search . '%')
                      ->orWhere('keterangan_keperluan', 'like', '%' . $this->search . '%');
                });
            })
            // Filter Dropdown
            ->when($this->satkerId, fn($q) => $q->where('satker_id', $this->satkerId))
            ->when($this->pendidikan, fn($q) => $q->where('pendidikan', $this->pendidikan))
            ->when($this->pekerjaan, fn($q) => $q->where('pekerjaan', $this->pekerjaan))
            // Filter Rentang Tanggal
            ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate));

        $surveys = $query->latest()->paginate($this->perPage);

       return view('livewire.admin.dashboard.survey-table', [
            'surveys' => $surveys,
            // Kirim opsi yang sudah difilter ke view
            'satkers' => $satkers,
            'filteredPendidikanOptions' => $filteredPendidikanOptions,
            'filteredPekerjaanOptions' => $filteredPekerjaanOptions,
        ]);
    }
}
