<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanItem extends Model
{
    protected $guarded = ['id'];

    public function kuesioner()
    {
        return $this->belongsTo(Kuesioner::class);
    }
}
