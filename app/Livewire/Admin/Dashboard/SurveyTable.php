<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\JawabanSurvey;
use App\Models\Satker;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Jobs\ExportRespondenJob;
use App\Models\Export;

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

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // Properti untuk UI
    public int $perPage = 5;
    public $satkers;
    public $pendidikanOptions = ['SD', 'SMP', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'];
    public $pekerjaanOptions = ['PNS/ASN', 'TNI/POLRI', 'Wiraswasta/Wirausaha', 'Pelajar/Mahasiswa', 'Honorer', 'Petani', 'Nelayan', 'Ibu Rumah Tangga', 'Lainnya'];

    public function mount()
    {
        // Ambil data untuk opsi filter
        $this->satkers = Satker::orderBy('nama_satker')->get();

        $this->endDate = Carbon::today()->toDateString();
        $this->startDate = Carbon::today()->subMonth()->toDateString();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
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

    public function export()
    {
        // Kumpulkan semua filter aktif
        $filters = [
            'search' => $this->search,
            'satkerId' => $this->satkerId,
            'pendidikan' => $this->pendidikan,
            'pekerjaan' => $this->pekerjaan,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ];

        // Buat record di tabel exports
        $export = auth()->user()->exports()->create();

        // Kirim pekerjaan ke antrean
        ExportRespondenJob::dispatch($export, auth()->user(), $filters);

        // Beri notifikasi ke pengguna
        $this->dispatch('show-export-notification');
    }

    public function render()
    {
        // Opsi untuk filter dropdown (tidak berubah)
        $satkers = Satker::query()
            ->when($this->searchSatker, fn($q) => $q->where('nama_satker', 'like', '%' . $this->searchSatker . '%'))
            ->orderBy('nama_satker')
            ->get();
        $filteredPendidikanOptions = collect($this->pendidikanOptions)->filter(fn($o) => stristr($o, $this->searchPendidikan));
        $filteredPekerjaanOptions = collect($this->pekerjaanOptions)->filter(fn($o) => stristr($o, $this->searchPekerjaan));

        // 1. Buat query dasar dengan filter
        $query = JawabanSurvey::with('satker')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama', 'like', '%' . $this->search . '%')
                        ->orWhere('keterangan_keperluan', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->satkerId, fn($q) => $q->where('satker_id', $this->satkerId))
            ->when($this->pendidikan, fn($q) => $q->where('pendidikan', $this->pendidikan))
            ->when($this->pekerjaan, fn($q) => $q->where('pekerjaan', $this->pekerjaan))
            ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate));

        // 2. Terapkan sorting di level database jika BUKAN 'nilai_ikm'
        if ($this->sortField !== 'nilai_ikm') {
            // Penanganan khusus untuk sort by nama satker
            if ($this->sortField === 'satkers.nama_satker') {
                $query->join('satkers', 'jawaban_surveys.satker_id', '=', 'satkers.id')
                    ->orderBy('satkers.nama_satker', $this->sortDirection)
                    ->select('jawaban_surveys.*'); // Penting agar tidak ada konflik kolom
            } else {
                // Untuk kolom lain di tabel utama
                $query->orderBy($this->sortField, $this->sortDirection);
            }
        }

        // 3. Lakukan paginasi
        $surveys = $query->paginate($this->perPage);

        // 4. Tambahkan nilai IKM ke setiap item setelah paginasi
        $surveys->getCollection()->transform(function ($survey) {
            $survey->nilai_ikm = $survey->hitungNilaiIkm();
            return $survey;
        });

        // 5. JIKA sorting berdasarkan 'nilai_ikm', urutkan koleksi yang sudah dipaginasi
        if ($this->sortField === 'nilai_ikm') {
            $items = $surveys->getCollection();
            $sortedItems = $this->sortDirection === 'asc' ? $items->sortBy('nilai_ikm') : $items->sortByDesc('nilai_ikm');

            // Buat ulang instance Paginator dengan data yang sudah diurutkan
            $surveys = new \Illuminate\Pagination\LengthAwarePaginator(
                $sortedItems,
                $surveys->total(),
                $surveys->perPage(),
                $surveys->currentPage(),
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        }

        return view('livewire.admin.dashboard.survey-table', [
            'surveys' => $surveys,
            'satkers' => $satkers,
            'filteredPendidikanOptions' => $filteredPendidikanOptions,
            'filteredPekerjaanOptions' => $filteredPekerjaanOptions,
        ]);
    }
}
