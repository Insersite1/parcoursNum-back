<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referant extends Model
{
    use HasFactory;


    public function structure()
{
    return $this->belongsTo(Structure::class);
}

}
