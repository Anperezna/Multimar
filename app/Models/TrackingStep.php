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
    ];

    public function incoterms()
    {
        return $this->hasMany(Incoterm::class, 'tracking_steps_id');
    }

    public function tipusTrackings()
    {
        return $this->hasMany(TipusTracking::class, 'tracking_steps_id');
    }
}