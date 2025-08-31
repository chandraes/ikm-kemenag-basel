<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kuesioner extends Model
{
    protected $fillable = ['pertanyaan', 'urutan', 'pilihan_jawaban',
    ];

    // Tambahkan $casts untuk konversi otomatis JSON <-> Array
    protected $casts = [
        'pilihan_jawaban' => 'array',
    ];
}
