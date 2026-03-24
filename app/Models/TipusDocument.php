<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipusDocument extends Model
{
    use HasFactory;

    protected $table = 'tipus_document';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'tipus_document',
    ];

    public function document()
    {
        return $this->hasOne(Document::class, 'id');
    }
}