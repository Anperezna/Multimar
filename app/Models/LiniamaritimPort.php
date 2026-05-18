<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiniamaritimPort extends Model
{
    protected $table = 'liniasmaritim_ports';

    public $timestamps = false;

    protected $fillable = [
        'linia_transport_maritim_id',
        'port_id',
        'nom_linia_transport_maritim',
    ];

    public function liniaTransportMaritim()
    {
        return $this->belongsTo(LiniaTransportMaritim::class, 'linia_transport_maritim_id');
    }

    public function port()
    {
        return $this->belongsTo(Port::class, 'port_id');
    }
}
