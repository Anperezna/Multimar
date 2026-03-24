<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'foto_user',
        'foto_dni_front',
        'foto_dni_back',
    ];

    public function solicituds()
    {
        return $this->hasMany(Solicitud::class, 'client_id');
    }
}