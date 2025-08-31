<?php

namespace App\Livewire\Kuesioner;

use App\Models\Kuesioner;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class ManajemenKuesioner extends Component
{
    use WithPagination;

    // Properti untuk Form
    public $kuesioner_id;
    public $pertanyaan;
    public $urutan = 0;
    public array $pilihan_jawaban = [];

    // Properti untuk Fungsionalitas UI
    public bool $isModalOpen = false;
    public string $search = '';
    public int $perPage = 5;
    public string $sortBy = 'urutan';
    public string $sortDirection = 'asc';

    /**
     * Hook untuk mereset paginasi saat $perPage diperbarui.
     */
    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    /**
     * Mengubah kolom dan arah sorting tabel.
     */
    public function sortingBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortBy = $field;
        $this->resetPage();
    }

    /**
     * Merender komponen.
     */
    public function render()
    {
        $kuesioners = Kuesioner::query()
            ->where('pertanyaan', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.kuesioner.manajemen-kuesioner', [
            'kuesioners' => $kuesioners
        ]);
    }

    /**
     * Mempersiapkan form untuk membuat data baru.
     */
    public function create()
    {
        $this->resetFields();
        $maxUrutan = Kuesioner::max('urutan');
        $this->urutan = $maxUrutan + 1;
        // Siapkan 4 pilihan jawaban kosong sebagai default
        $this->pilihan_jawaban = [
            ['nilai' => 1, 'label' => ''],
            ['nilai' => 2, 'label' => ''],
            ['nilai' => 3, 'label' => ''],
            ['nilai' => 4, 'label' => ''],
        ];
        $this->openModal();
    }

    /**
     * Mengisi form dengan data yang ada untuk diedit.
     */
    public function edit($id)
    {
        $kuesioner = Kuesioner::findOrFail($id);
        $this->kuesioner_id = $id;
        $this->pertanyaan = $kuesioner->pertanyaan;
        $this->urutan = $kuesioner->urutan;
        $this->pilihan_jawaban = $kuesioner->pilihan_jawaban; // Otomatis menjadi array karena $casts
        $this->openModal();
    }

    /**
     * Menampilkan konfirmasi sebelum menyimpan.
     */
    public function triggerConfirm()
    {
        $this->validate();
        $message = $this->kuesioner_id ? 'Anda yakin ingin memperbarui pertanyaan ini?' : 'Anda yakin ingin menyimpan pertanyaan baru ini?';
        LivewireAlert::title($message)
            ->withConfirmButton(true)->withCancelButton(true)
            ->confirmButtonText('Ya, Lanjutkan')->cancelButtonText('Batal')
            ->onConfirm('save')->timer(null)->show();
    }

    /**
     * [Listener] Menyimpan data dan mengatur urutan.
     */
    #[On('save')]
    public function save()
    {
        $validatedData = $this->validate();

        try {
            DB::transaction(function () use ($validatedData) {
                $maxUrutan = Kuesioner::max('urutan') ?? 0;
                $newUrutan = (int) $validatedData['urutan'];

                $limit = $this->kuesioner_id ? $maxUrutan : $maxUrutan + 1;
                if ($newUrutan > $limit) {
                    $newUrutan = $limit;
                    $validatedData['urutan'] = $newUrutan;
                }

                if ($this->kuesioner_id) {
                    $kuesioner = Kuesioner::find($this->kuesioner_id);
                    $oldUrutan = $kuesioner->urutan;

                    if ($newUrutan != $oldUrutan) {
                        if ($newUrutan < $oldUrutan) {
                            Kuesioner::where('urutan', '>=', $newUrutan)
                                      ->where('urutan', '<', $oldUrutan)
                                      ->increment('urutan');
                        } else {
                            Kuesioner::where('urutan', '>', $oldUrutan)
                                      ->where('urutan', '<=', $newUrutan)
                                      ->decrement('urutan');
                        }
                    }
                } else {
                    Kuesioner::where('urutan', '>=', $newUrutan)->increment('urutan');
                }

                Kuesioner::updateOrCreate(['id' => $this->kuesioner_id], $validatedData);
            });

            $message = $this->kuesioner_id ? 'Pertanyaan berhasil diperbarui.' : 'Pertanyaan berhasil ditambahkan.';
            LivewireAlert::title($message)->success()->show();

            $this->closeModal();
            $this->resetFields();

        } catch (\Exception $e) {
            LivewireAlert::title('Terjadi Kesalahan')->text('Gagal menyimpan data. Error: ' . $e->getMessage())->error()->show();
        }
    }

    /**
     * Menampilkan konfirmasi sebelum menghapus.
     */
    public function confirmDelete($id)
    {
        LivewireAlert::title('Anda yakin ingin menghapus pertanyaan ini?')
            ->text('Data yang dihapus tidak dapat dikembalikan.')
            ->withConfirmButton(true)->withCancelButton(true)
            ->confirmButtonText('Ya, Hapus')->cancelButtonText('Batal')
            ->onConfirm('delete', ['id' => $id])->timer(null)->show();
    }

    /**
     * [Listener] Menghapus data dan menyesuaikan urutan.
     */
    #[On('delete')]
    public function delete($payload)
    {
        try {
            DB::transaction(function () use ($payload) {
                $kuesioner = Kuesioner::findOrFail($payload['id']);
                $deletedUrutan = $kuesioner->urutan;
                $kuesioner->delete();
                Kuesioner::where('urutan', '>', $deletedUrutan)->decrement('urutan');
            });
            LivewireAlert::title('Pertanyaan berhasil dihapus.')->success()->show();
        } catch (\Exception $e) {
            LivewireAlert::title('Terjadi Kesalahan')->text('Gagal menghapus data.')->error()->show();
        }
    }

    // --- Method untuk form dinamis ---
    public function addPilihanJawaban()
    {
        $nextValue = count($this->pilihan_jawaban) + 1;
        $this->pilihan_jawaban[] = ['nilai' => $nextValue, 'label' => ''];
    }

    public function removePilihanJawaban($index)
    {
        unset($this->pilihan_jawaban[$index]);
        $this->pilihan_jawaban = array_values($this->pilihan_jawaban);
        foreach ($this->pilihan_jawaban as $key => &$pilihan) {
            $pilihan['nilai'] = $key + 1;
        }
    }

    /**
     * Aturan validasi.
     */
    protected function rules(): array
    {
        return [
            'pertanyaan' => 'required|string|min:10',
            'urutan' => 'required|integer|min:1',
            'pilihan_jawaban' => 'required|array|min:2',
            'pilihan_jawaban.*.label' => 'required|string|max:100',
            'pilihan_jawaban.*.nilai' => 'required|integer',
        ];
    }

    // --- Helper Methods ---
    public function openModal() { $this->isModalOpen = true; }
    public function closeModal() { $this->isModalOpen = false; }
    private function resetFields()
    {
        $this->reset('kuesioner_id', 'pertanyaan', 'urutan', 'pilihan_jawaban');
        $this->resetErrorBag();
    }
}
