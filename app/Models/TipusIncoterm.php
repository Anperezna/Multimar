<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipusIncoterm extends Model
{
    protected $table = 'tipus_incoterms';
    
    // 🔥 ESTA ES LA LÍNEA MÁGICA QUE LO ARREGLA
    public $timestamps = false; 

    protected $fillable = ['codi', 'nom'];

    public function trackingSteps(): BelongsToMany
    {
        return $this->belongsToMany(
            TrackingStep::class,
            'incoterms',
            'tipus_inconterm_id',
            'tracking_steps_id'
        )
        ->orderBy('ordre') // Añades el primer orden aquí
        ->orderBy('id');   // Añades el segundo orden aquí
    }
}