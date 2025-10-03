<?php

namespace App\Livewire\Satker;

use App\Models\Satker;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class ManajemenSatker extends Component
{
    use WithPagination;

    public $satker_id;
    public $nama_satker;
    public $isModalOpen = false;
    public $search = '';

    // 1. Properti untuk sorting
    public string $sortBy = 'nama_satker';
    public string $sortDirection = 'asc';

    public int $perPage = 10;

    public bool $isShareModalOpen = false;
    public ?Satker $selectedSatkerForShare = null;
    public string $shareableUrl = '';
    public string $shareableText = '';

    // 3. Hook untuk mereset paginasi saat $perPage berubah
    public function updatingPerPage(): void
    {
        $this->resetPage();
    }
    // 2. Method untuk mengubah sorting
    public function sortingBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortBy = $field;
        $this->resetPage(); // Reset paginasi saat sorting diubah
    }

    public function render()
    {
        $satkers = Satker::query()
            ->where('nama_satker', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortBy, $this->sortDirection)
            // 2. Gunakan properti $perPage di sini
            ->paginate($this->perPage);

        return view('livewire.satker.manajemen-satker', [
            'satkers' => $satkers
        ]);
    }


    public function create()
    {
        $this->resetFields();
        $this->openModal();
    }

    public function edit($id)
    {
        $satker = Satker::findOrFail($id);
        $this->satker_id = $id;
        $this->nama_satker = $satker->nama_satker;
        $this->openModal();
    }

    public function triggerConfirm()
    {
        $this->validate([
            'nama_satker' => 'required|string|max:255|unique:satkers,nama_satker,' . $this->satker_id,
        ]);

        $message = $this->satker_id ? 'Anda yakin ingin memperbarui program ini?' : 'Anda yakin ingin menyimpan program baru ini?';

        LivewireAlert::title($message)
            ->withConfirmButton(true)
            ->withCancelButton(true)
            ->confirmButtonText('Ya, Lanjutkan')
            ->cancelButtonText('Batal')
            ->onConfirm('save')
            ->info()
            ->timer(null)
            ->show();
    }

    #[On('save')]
    public function save()
    {
        $validatedData = $this->validate([
            'nama_satker' => 'required|string|max:255|unique:satkers,nama_satker,' . $this->satker_id,
        ]);

        Satker::updateOrCreate(['id' => $this->satker_id], $validatedData);

        $message = $this->satker_id ? 'Nama Program berhasil diperbarui.' : 'Program berhasil ditambahkan.';
        LivewireAlert::title($message)->success()->show();

        $this->closeModal();
        $this->resetFields();
    }

    public function confirmDelete($id)
    {
        LivewireAlert::title('Anda yakin ingin menghapus program ini?')
            ->text('Data yang dihapus tidak dapat dikembalikan.')
            ->withConfirmButton(true)
            ->withCancelButton(true)
            ->confirmButtonText('Ya, Hapus')
            ->cancelButtonText('Batal')
            ->onConfirm('delete', ['id' => $id])
            ->timer(null)
            ->warning()
            ->show();
    }

    #[On('delete')]
    public function delete($payload)
    {
        Satker::find($payload['id'])->delete();
        LivewireAlert::title('Program berhasil dihapus.')->success()->show();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function openShareModal($satkerId)
    {
        $this->selectedSatkerForShare = Satker::findOrFail($satkerId);

        $this->shareableUrl = route('survey.form', ['satker' => $this->selectedSatkerForShare->id]);

        $this->shareableText = rawurlencode(
            "Silakan isi Survei Kepuasan Masyarakat untuk layanan kami di " .
                $this->selectedSatkerForShare->nama_satker . ". Partisipasi Anda sangat berarti!\n\n" .
                $this->shareableUrl
        );

        $this->isShareModalOpen = true;

        // GANTI dispatch DENGAN return $this->js()
        // Ini akan dieksekusi di browser setelah modal ditampilkan
        return $this->js("generateQrCode('" . $this->shareableUrl . "')");
    }

    // =======================================================
    // TAMBAHKAN: Method baru untuk menutup modal share
    // =======================================================
    public function closeShareModal()
    {
        $this->isShareModalOpen = false;
        $this->selectedSatkerForShare = null;
    }

    private function resetFields()
    {
        $this->satker_id = null;
        $this->nama_satker = '';
        $this->resetErrorBag();
    }
}
