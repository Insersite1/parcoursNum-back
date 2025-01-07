<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;
    protected $fillable = ['structure_id', 'dispositif_id'];

    public function structure()
    {
        return $this->belongsTo(Structure::class);
    }

    public function dispositif()
    {
        return $this->belongsTo(Dispositif::class);
    }
}
