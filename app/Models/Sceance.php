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



public function jeunes()
{
    return $this->belongsToMany(User::class, 'jeune_sceance', 'sceance_id', 'user_id');
}

}
