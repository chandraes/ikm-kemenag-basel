<?php

namespace App\Console\Commands;

use App\Models\Export;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PruneOldExports extends Command
{
    protected $signature = 'exports:prune';
    protected $description = 'Delete old export files and records from the database';

    public function handle()
    {
        // Tentukan batas waktu (misal: 30 hari yang lalu)
        $cutoffDate = Carbon::now()->subDays(1);

        // Cari semua record ekspor yang lebih lama dari batas waktu
        $oldExports = Export::where('created_at', '<', $cutoffDate)->get();

        if ($oldExports->isEmpty()) {
            $this->info('No old exports to prune.');
            return;
        }

        $this->info("Found {$oldExports->count()} old export(s) to prune...");

        foreach ($oldExports as $export) {
            // Hapus file fisik dari storage
            if ($export->file_path && Storage::disk('local')->exists($export->file_path)) {
                Storage::disk('local')->delete($export->file_path);
                $this->line("Deleted file: {$export->file_path}");
            }

            // Hapus record dari database
            $export->delete();
        }

        $this->info('Successfully pruned all old exports.');
    }
}
