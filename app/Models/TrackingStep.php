<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingStep extends Model
{
    use HasFactory;

    protected $table = 'tracking_steps';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'ordre',
        'nom',
        'incoterm_id',
        'activo',
    ];

    /**
     * Cast attributes to native types for JSON responses and model access.
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    public function incoterm()
    {
        return $this->belongsTo(Incoterm::class, 'incoterm_id');
    }

    public function tipusTrackings()
    {
        return $this->hasMany(TipusTracking::class, 'tracking_steps_id');
    }
}