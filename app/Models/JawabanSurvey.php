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

     /**
     * Menghitung nilai Indeks Kepuasan Masyarakat (IKM) per responden.
     * Standar IKM terbaru mengalikan nilai rata-rata tertimbang dengan 25.
     *
     * @return float
     */
    public function hitungNilaiIkm(): float
    {
        // Ambil semua jawaban item yang terkait dengan survei ini
        $jawabanItems = $this->jawabanItems;

        // Jika tidak ada jawaban, kembalikan nilai 0 untuk menghindari error
        if ($jawabanItems->isEmpty()) {
            return 0.0;
        }

        // Hitung nilai rata-rata. Asumsi setiap pertanyaan memiliki bobot yang sama.
        $rataRata = $jawabanItems->avg('jawaban_nilai');

        // Kalikan dengan nilai konversi IKM (25)
        $nilaiIkm = $rataRata * 25;

        // Kembalikan nilai dengan dua angka di belakang koma
        return round($nilaiIkm, 2);
    }
}
