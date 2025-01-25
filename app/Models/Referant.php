<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referant extends Model
{
    use HasFactory;



    protected $fillable = [
        'avatar', 'nom', 'Prenom', 'email', 'numTelephone', 'password', 
        'statut', 'sexe', 'Adresse', 'structure_id', 'role_id'
    ];
    public function structure()
{
    return $this->belongsTo(Structure::class);
}

}
