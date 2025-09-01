<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanSurvey extends Model
{
    protected $fillable = [
        'satker_id',
        'nama',
        'email',
        'usia',
        'alamat_lengkap',
        'keterangan_keperluan',
        'kritik_saran',
        'jenis_kelamin',
        'agama',
        'pendidikan',
        'pekerjaan',
        'pekerjaan_lainnya',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Satker.
     */
    public function satker()
    {
        return $this->belongsTo(Satker::class);
    }

    public function jawabanItems()
    {
        return $this->hasMany(JawabanItem::class);
    }
}
