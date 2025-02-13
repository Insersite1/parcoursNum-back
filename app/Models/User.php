<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Contracts\Providers\JWT;

class User extends Authenticatable implements JWTSubject
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

            'avatar',
            'nom',
            'Prenom',
            'email',
            'numTelephone',
            'email_verified_at',
            'password',
            'region',
            'ville',
            'bibiographie',
            'dateNaissance',
            'codePostal',
            'NumSecuriteSocial',
            'statut',
            'etat',
            'situation',
            'sexe',
            'etatCivil',
            'situationTravail',
            'QP',
            'ZRR',
            'ETH',
            'EPC',
            'API',
            'AE',
            'Adresse',
            'role_id',
            'structure_id',
            'accepter_conditions',
            'dispositif_id',
            'dateNaissance',
    ];

    public function structure()
{
    return $this->belongsTo(Structure::class);
}

public function dispositif()
{
    return $this->belongsTo(Dispositif::class, 'dispositif_id');
}

    /**
     * Relation avec le modèle Action
     */
   public function actions()
    {
        return $this->hasMany(Action::class, 'user_id');
    }
    public function role()
    {
        return $this->belongsTo(Role::class,'role_id');
    }
    public function session()
    {
        return $this->hasMany(Action::class,'action_id');
    }

   


    public function getAvatarUrlAttribute()
    {
    return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }
    public function sceances()
    {
        return $this->hasMany(Sceance::class, 'user_id');
    }



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        // TODO: Implement getJWTIdentifier() method.
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        // TODO: Implement getJWTCustomClaims() method.
        return [];
    }
}
