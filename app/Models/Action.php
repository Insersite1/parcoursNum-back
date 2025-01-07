<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    protected$fillable=[
        'nom',
        'place',
        'couverture',
        'user_id',
        'structure_id',
        'date_debut',
        'type',
        'couleur',
        'date_fin',
        'desciption',
        ];

        Public function user()
        {
            return $this->belongsTo(User::class,'user_id');
        }

        Public function structureDispositif()
        {
            return $this->belongsTo(StructureDispositif::class,'structureDispositif_id');
        }
}
