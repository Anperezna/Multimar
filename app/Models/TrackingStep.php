<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TrackingStep extends Model
{
    protected $table = 'tracking_steps';

    protected $fillable = ['nom', 'ordre'];

    /**
     * Relación Muchos a Muchos con TipusIncoterm a través de la pivot 'incoterms'
     */
    public function tipusIncoterms(): BelongsToMany
    {
        return $this->belongsToMany(
            TipusIncoterm::class,
            'incoterms',
            'tracking_steps_id',
            'tipus_inconterm_id'
        ); // 🔥 quitamos el ->withTimestamps()
    }
}