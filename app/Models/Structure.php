<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Structure extends Model
{
    use HasFactory;
    protected $fillable = ['couverture', 'nomcomplet', 'dateExpire', 'statut'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function referant()
    {
        return $this->hasMany(referant::class,'user_id');
    }
    public function dispositifs()
    {
        return $this->belongsToMany(Dispositif::class, 'structure_dispositif');
    }
    public function actions()
    {
    return $this->belongsToMany(Action::class, 'structure_action');
    }

}
