<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use HasFactory;

    protected $table = 'paissos';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'nom',
    ];

    public function ciutats()
    {
        return $this->hasMany(Ciutat::class, 'pais_id');
    }
}