<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'id',
        'nom',
        'tipus_document_id',
    ];

    public function tipusDocument()
    {
        return $this->belongsTo(TipusDocument::class, 'id');
    }

    public function oferta()
    {
        return $this->hasOne(Oferta::class, 'id');
    }
}