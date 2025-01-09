<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispositif extends Model
{
    use HasFactory;
    protected $fillable = ['couverture', 'name', 'DateDebut', 'statut', 'DateFin','pays'];

}
