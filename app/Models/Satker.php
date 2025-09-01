<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satker extends Model
{
    protected $fillable = ['nama_satker'];

    public function jawabanSurveys()
    {
        return $this->hasMany(JawabanSurvey::class);
    }

    /**
     * Mendefinisikan relasi "hasMany" ke model JawabanItem.
     */
    public function jawabanItems()
    {
        return $this->hasMany(JawabanItem::class);
    }
}
