<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiniaTransportMaritim extends Model
{
    use HasFactory;

    protected $table = 'linies_transport_maritim';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'nom',
        'ciutat_id',
    ];

    public function ciutat()
    {
        return $this->belongsTo(Ciutat::class, 'ciutat_id');
    }

    public function ofertes()
    {
        return $this->hasMany(Oferta::class, 'linia_transport_maritim_id');
    }

    public function ports()
    {
        return $this->belongsToMany(Port::class, 'liniasmaritim_ports', 'linia_transport_maritim_id', 'port_id')
            ->withPivot('nom_linia_transport_maritim');
    }
}