<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanSurvey extends Model
{
     protected $fillable = [
        'satker_id', 'nama', 'email', 'usia',
        'alamat_lengkap', 'keterangan_keperluan', 'kritik_saran',
    ];
}
