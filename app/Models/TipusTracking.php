<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipusTracking extends Model
{
    use HasFactory;

    protected $table = 'tipus_tracking';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'tipus_nom',
        'tracking_steps_id',
    ];

    public function trackingStep()
    {
        return $this->belongsTo(TrackingStep::class, 'tracking_steps_id');
    }
}