<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos_mensajes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mensaje_id')->constrained('mensajes')->cascadeOnDelete();
            $table->string('tipo_evento', 20);
            $table->timestamp('ocurrido_en');
            $table->uuid('actor_usuario_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadatos')->nullable();

            $table->index('mensaje_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_mensajes');
    }
};
