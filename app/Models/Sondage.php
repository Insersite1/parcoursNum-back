<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sondage extends Model
{
    use HasFactory;
    protected $fillable = [
        'titre',
        'description',
        'date_debut',
        'date_fin',
        'est_publie',
        'pour_tous_utilisateurs',
        'statut',
        'user_id'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'est_public' => 'boolean',
        'pour_tous_utilisateurs' => 'boolean'
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

   
    /**
     * Relation avec le model User : Un sondage est crée par un référent.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
