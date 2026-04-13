<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model
{
    use HasFactory;

    protected $table = 'chatbot_messages';

    protected $fillable = [
        'usuari_id',
        'provider',
        'model',
        'prompt',
        'reply',
        'status',
        'error',
    ];
}
