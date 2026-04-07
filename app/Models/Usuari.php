<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuari extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'usuaris';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'correu',
        'contrasenya',
        'nom',
        'cognoms',
        'rol_id',
        'pais_id',
        'empresa',
        'dni',
        'foto_user',
        'foto_dni_front',
        'foto_dni_back',
        'usuari_id',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Usuari::class, 'usuari_id');
    }

    public function subordinats()
    {
        return $this->hasMany(Usuari::class, 'usuari_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'usuari_id');
    }

    public function solicitudsOperador()
    {
        return $this->hasMany(Solicitud::class, 'operador_id');
    }
}