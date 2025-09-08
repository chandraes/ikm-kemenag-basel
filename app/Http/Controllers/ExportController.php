<?php
namespace App\Http\Controllers;

use App\Models\Export;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function download(Export $export)
    {
        // Pastikan hanya user yang benar yang bisa download
        if (auth()->user()->id !== $export->user_id) {
            abort(403);
        }

        return Storage::disk('local')->download($export->file_path);
    }
}
