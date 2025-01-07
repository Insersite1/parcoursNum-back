<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StructureDispositif extends Model
{
    use HasFactory;

    protected $fillable = ['structure_id', 'dispositif_id'];

    public function action()
    {
        return $this->hasMany(Action::class,'action_id');
    }

    public function structure()
    {
        return $this->belongsTo(Structure::class,'structure_id');
    }
    public function dispositif()
    {
        return $this->belongsTo(Dispositif::class,'dispositif_id');
    }
}
