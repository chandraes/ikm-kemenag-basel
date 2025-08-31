<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class PengaturanAplikasi extends Component
{
    use WithFileUploads;

    public $nama_instansi;
    public $logo; // Properti untuk file upload
    public $existingLogo;

    public function mount()
    {
        $this->nama_instansi = Setting::where('key', 'nama_instansi')->value('value');
        $this->existingLogo = Setting::where('key', 'logo_path')->value('value');
    }

    public function save()
    {
        $this->validate([
            'nama_instansi' => 'required|string|max:255',
            'logo' => 'nullable|image|max:1024', // 1MB Max
        ]);

        // Simpan nama instansi
        Setting::updateOrCreate(
            ['key' => 'nama_instansi'],
            ['value' => $this->nama_instansi]
        );

        // Jika ada logo baru diupload
        if ($this->logo) {
            // Hapus logo lama jika ada
            if ($this->existingLogo && Storage::disk('public')->exists($this->existingLogo)) {
                Storage::disk('public')->delete($this->existingLogo);
            }

            // Simpan logo baru dan dapatkan path-nya
            $path = $this->logo->store('logos', 'public');

            // Simpan path logo baru ke database
            Setting::updateOrCreate(
                ['key' => 'logo_path'],
                ['value' => $path]
            );

            $this->existingLogo = $path;
            $this->logo = null; // Reset file input
        }
        
        Cache::forget('app_settings');

        LivewireAlert::title('Berhasil','Pengaturan telah berhasil disimpan.')->success();
    }

    public function render()
    {
        return view('livewire.settings.pengaturan-aplikasi');
    }
}
