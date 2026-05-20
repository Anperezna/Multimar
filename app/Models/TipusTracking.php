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
        'tipus_incoterm_id',
        'tracking_step_id',
    ];

    public function tipusIncoterm()
    {
        return $this->belongsTo(TipusIncoterm::class, 'tipus_incoterm_id');
    }

    public function trackingStep()
    {
        return $this->belongsTo(TrackingStep::class, 'tracking_step_id');
    }
}
