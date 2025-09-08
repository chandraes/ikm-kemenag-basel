<?php

namespace App\Jobs;

use App\Events\ExportReady;
use App\Models\Export;
use App\Models\JawabanSurvey;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExportRespondenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Export $export,
        public User $user,
        public array $filters
    ) {}

    public function handle(): void
    {
        $fileName = 'exports/laporan-responden-' . $this->export->id . '-' . now()->timestamp . '.csv';
        Storage::disk('local')->put($fileName, ''); // Buat file kosong
        $filePath = Storage::disk('local')->path($fileName);
        $file = fopen($filePath, 'w');

        // Tulis Header
        $columns = ['Nama Responden', 'Unit Layanan', 'Pendidikan', 'Pekerjaan', 'Nilai IKM', 'Tanggal Survei', 'Kritik & Saran'];
        fputcsv($file, $columns);

        // Query dengan filter
        $query = JawabanSurvey::with('satker')
            ->when($this->filters['search'] ?? null, fn($q, $v) => $q->where('nama', 'like', "%{$v}%"))
            ->when($this->filters['satkerId'] ?? null, fn($q, $v) => $q->where('satker_id', $v))
            ->when($this->filters['startDate'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($this->filters['endDate'] ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderBy($this->filters['sortField'] ?? 'created_at', $this->filters['sortDirection'] ?? 'desc');

        // Proses per-batch (chunk) agar tidak membebani memori
        $query->chunk(1000, function ($surveys) use ($file) {
            foreach ($surveys as $survey) {
                fputcsv($file, [
                    $survey->nama,
                    $survey->satker?->nama_satker ?? 'N/A',
                    $survey->pendidikan,
                    $survey->pekerjaan === 'Lainnya' ? $survey->pekerjaan_lainnya : $survey->pekerjaan,
                    number_format($survey->hitungNilaiIkm(), 2),
                    $survey->created_at->isoFormat('D MMMM YYYY, HH:mm'),
                    $survey->kritik_saran
                ]);
            }
        });

        fclose($file);

        // Update status di database
        $this->export->update([
            'status' => 'completed',
            'file_path' => $fileName,
            'completed_at' => now(),
        ]);

        // Kirim notifikasi ke pengguna
        broadcast(new ExportReady($this->user, $this->export));
    }

    public function failed(Throwable $exception): void
    {
        $this->export->update(['status' => 'failed']);
        // (Opsional) Kirim notifikasi kegagalan
    }
}
