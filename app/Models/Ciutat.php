<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciutat extends Model
{
    use HasFactory;

    protected $table = 'ciutats';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'nom',
        'pais_id',
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    public function liniesTransportMaritim()
    {
        return $this->hasMany(LiniaTransportMaritim::class, 'ciutat_id');
    }

    public function ports()
    {
        return $this->hasMany(Port::class, 'ciutat_id');
    }

    public function transportistes()
    {
        return $this->hasMany(Transportista::class, 'ciutat_id');
    }

    public function solicitudsOrigen()
    {
        return $this->hasMany(Solicitud::class, 'origen_id');
    }

    public function solicitudsDestino()
    {
        return $this->hasMany(Solicitud::class, 'destino_id');
    }
}