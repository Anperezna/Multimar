<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuari_id')->nullable()->index();
            $table->string('provider', 40);
            $table->string('model', 120)->nullable();
            $table->text('prompt');
            $table->longText('reply')->nullable();
            $table->string('status', 20)->default('ok');
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
    }
};
