<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rols';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'rol',
    ];

    public function usuaris()
    {
        return $this->hasMany(Usuari::class, 'rol_id');
    }
}