<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionUser extends Model
{
    use HasFactory;
    protected $table = 'action_user';
    protected $fillable = ['action_id', 'user_id'];
    public function action()
    {
        return $this->belongsTo(Action::class,'action_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

}
