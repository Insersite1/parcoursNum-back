<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Structure extends Model
{
    use HasFactory;
    protected $fillable = ['couverture', 'nomcomplet', 'dateExpire', 'statut', 'dispositif_id'];

    public function dispositif()
    {
        return $this->belongsTo(Dispositif::class, 'dispositif_id');
    }
}
