<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipusIncoterm extends Model
{
    use HasFactory;

    protected $table = 'tipus_incoterms';

    public $timestamps = false;

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'codi',
        'nom',
    ];

    public function incoterms()
    {
        return $this->hasMany(Incoterm::class, 'tipus_inconterm_id');
    }

    public function tipusTrackings()
    {
        return $this->hasMany(TipusTracking::class, 'tipus_incoterm_id');
    }

    public function trackingSteps()
    {
        return $this->belongsToMany(
            TrackingStep::class,
            'tipus_tracking',
            'tipus_incoterm_id',
            'tracking_step_id',
            'id',
            'id'
        )->withPivot('id');
    }
}
