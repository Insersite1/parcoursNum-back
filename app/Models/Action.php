<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    protected $fillable=[
        'nom',
        'place',
        'couverture',
        'user_id',
        'structure_dispositif_id',
        'date_debut',
        'type',
        'couleur',
        'date_fin',
        'description',
        'auteur'
        ];


    /**
     * Relation avec le modèle User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

     // Relation avec la table actionuser
    public function actionUser()
    {
        return $this->hasMany(ActionUser::class, 'action_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'action_user', 'action_id', 'user_id');
    }

        public function dispositif()
    {
        return $this->belongsTo(Dispositif::class);
    }

     /**
     * Relation avec le modèle StructureDispositif
     */

    public function structureDispositif()
    {
        return $this->belongsTo(StructureDispositif::class, 'structure_dispositif_id');
    }

}
