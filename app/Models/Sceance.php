<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sceance extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function session()
    {
        return $this->belongsTo(Session::class,'session_id');
    }
}
