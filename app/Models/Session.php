<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    protected $fillable = ['nom', 'image', 'date_debut', 'date_fin','file','par','action_id','description'];


    public function seances()
    {
        return $this->hasMany(Sceance::class,'seance_id');
    }

    public function action()
    {
        return $this->belongsTo(Action::class,'action_id');
    }

    
}
