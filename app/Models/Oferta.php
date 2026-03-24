<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    use HasFactory;

    protected $table = 'ofertes';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'tipus_transport_id',
        'comentaris',
        'agent_comercial_id',
        'transportista_origen_id',
        'tipus_validacio_id',
        'port_origen_id',
        'port_desti_id',
        'linia_transport_maritim_id',
        'estat_oferta_id',
        'data_creacio',
        'data_validessa_inicial',
        'data_validessa_fina',
        'rao_rebuig',
        'solicitud_id',
        'documents_id',
        'etd',
        'eta',
        'transportista_destino_id',
        'operador_id',
        'acceptat',
        'vist',
        'acabat',
        'cancelat',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'id');
    }

    public function documentAdjunt()
    {
        return $this->belongsTo(Document::class, 'documents_id');
    }

    public function estatOferta()
    {
        return $this->belongsTo(EstatOferta::class, 'estat_oferta_id');
    }

    public function liniaTransportMaritim()
    {
        return $this->belongsTo(LiniaTransportMaritim::class, 'linia_transport_maritim_id');
    }

    public function portOrigen()
    {
        return $this->belongsTo(Port::class, 'port_origen_id');
    }

    public function portDesti()
    {
        return $this->belongsTo(Port::class, 'port_desti_id');
    }

    public function tipusTransport()
    {
        return $this->belongsTo(TipusTransport::class, 'tipus_transport_id');
    }

    public function tipusValidacio()
    {
        return $this->belongsTo(TipusValidacio::class, 'tipus_validacio_id');
    }

    public function transportistaOrigen()
    {
        return $this->belongsTo(Transportista::class, 'transportista_origen_id');
    }

    public function transportistaDestino()
    {
        return $this->belongsTo(Transportista::class, 'transportista_destino_id');
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }
}