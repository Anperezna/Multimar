<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipusCarrega extends Model
{
    use HasFactory;

    protected $table = 'tipus_carrega';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'tipus',
    ];

    public function solicituds()
    {
        return $this->hasMany(Solicitud::class, 'tipus_carrega_id');
    }
}