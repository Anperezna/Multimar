<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'titulo',
        'descripcion',
        'visto',
        'usuari_id',
    ];

    public function usuari()
    {
        return $this->belongsTo(Usuari::class, 'usuari_id');
    }
}