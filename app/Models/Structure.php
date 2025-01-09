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
        return $this->hasMany(User::class, 'structure_id');
    }
    

    public function referant()
    {
        return $this->hasMany(referant::class,'user_id');
    }

    public function structureDispositif()
    {
        return $this->hasMany(StructureDispositif::class,'structureDispositif_id');
    }
}
