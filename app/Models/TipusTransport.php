<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipusTransport extends Model
{
    use HasFactory;

    protected $table = 'tipus_transports';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'tipus',
    ];

    public function solicituds()
    {
        return $this->hasMany(Solicitud::class, 'tipus_transport_id');
    }

    public function ofertes()
    {
        return $this->hasMany(Oferta::class, 'tipus_transport_id');
    }
}