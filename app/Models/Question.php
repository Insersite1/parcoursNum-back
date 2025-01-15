<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'sondage_id',
        'titre_question',
        'type_question',
        'options',
        'obligatoire'
    ];

    protected $casts = [
        'options' => 'array',
        'obligatoire' => 'boolean'
    ];

    public function sondage()
    {
        return $this->belongsTo(Sondage::class);
    }

    public function reponses()
    {
        return $this->hasMany(Reponse::class);
    }
}
