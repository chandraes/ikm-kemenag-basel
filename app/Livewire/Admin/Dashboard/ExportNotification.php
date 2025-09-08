<?php

namespace App\Livewire\Admin\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth; // <-- 1. TAMBAHKAN USE STATEMENT INI
use App\Models\Export; // <-- Pastikan ini ada
use Illuminate\Support\Facades\Storage; // <-- Tambahkan ini

class ExportNotification extends Component
{
    public $exports;

    protected function getListeners()
    {
        // Pastikan ada user yang login sebelum membuat listener
        if (Auth::check()) {
            return [
                // 2. UBAH auth()->id() MENJADI Auth::id()
                'echo-private:exports.' . Auth::id() . ',ExportReady' => 'loadExports',
                'show-export-notification' => 'loadExports',
            ];
        }
        return [];
    }

    public function mount()
    {
        $this->loadExports();
    }

    public function loadExports()
    {
        // Menggunakan Auth::user() juga lebih aman di sini
        if (Auth::check()) {
            $this->exports = Auth::user()->exports()->latest()->take(5)->get();
        } else {
            $this->exports = collect(); // Kembalikan koleksi kosong jika tidak ada user
        }
    }

    public function deleteExport($exportId)
    {
        $export = Export::find($exportId);

        // Keamanan: Pastikan hanya pemilik yang bisa menghapus
        if ($export && $export->user_id === Auth::id()) {
            // Hapus file dari storage jika ada
            if ($export->file_path && Storage::disk('local')->exists($export->file_path)) {
                Storage::disk('local')->delete($export->file_path);
            }
            // Hapus record dari database
            $export->delete();

            // Muat ulang daftar ekspor agar UI ter-update
            $this->loadExports();
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard.export-notification');
    }
}
