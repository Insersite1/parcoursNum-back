<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispositif extends Model
{
    use HasFactory;
    protected $fillable = ['couverture', 'name', 'DateDebut', 'statut', 'DateFin','pays'];

    // Relation avec le modèle Structure
    public function structures()
    {
        return $this->belongsToMany(Structure::class, 'structure_dispositifs');
    }

    public function jeunes()
    {
        return $this->hasMany(User::class,'dispositif_id');
    }

}
