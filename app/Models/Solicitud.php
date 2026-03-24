<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitud';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'mercancia_nombre',
        'pes_brut',
        'volum',
        'client_id',
        'operador_id',
        'mercancia_tipus',
        'tipus_transport_id',
        'tipus_contenidor_id',
        'origen_id',
        'destino_id',
        'incoterm_id',
        'tipus_fluxe_id',
        'tipus_carrega_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function operador()
    {
        return $this->belongsTo(Usuari::class, 'operador_id');
    }

    public function tipusTransport()
    {
        return $this->belongsTo(TipusTransport::class, 'tipus_transport_id');
    }

    public function tipusContenidor()
    {
        return $this->belongsTo(TipusContenidor::class, 'tipus_contenidor_id');
    }

    public function origen()
    {
        return $this->belongsTo(Ciutat::class, 'origen_id');
    }

    public function destino()
    {
        return $this->belongsTo(Ciutat::class, 'destino_id');
    }

    public function incoterm()
    {
        return $this->belongsTo(Incoterm::class, 'incoterm_id');
    }

    public function tipusFluxe()
    {
        return $this->belongsTo(TipusFluxe::class, 'tipus_fluxe_id');
    }

    public function tipusCarrega()
    {
        return $this->belongsTo(TipusCarrega::class, 'tipus_carrega_id');
    }

    public function ofertes()
    {
        return $this->hasMany(Oferta::class, 'solicitud_id');
    }
}