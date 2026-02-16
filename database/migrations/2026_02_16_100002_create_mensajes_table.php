<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('casilla_id')->constrained('casillas')->cascadeOnDelete();
            $table->string('remitente_nombre');
            $table->string('remitente_entidad')->nullable();
            $table->string('destinatario_nombre');
            $table->string('destinatario_tipo_doc', 10);
            $table->string('destinatario_num_doc', 20);
            $table->string('asunto');
            $table->text('cuerpo')->nullable();
            $table->timestamp('registrado_en');
            $table->string('codigo_estado', 20)->default('DEPOSITED');
            $table->string('etiqueta_estado', 30)->default('SIN LEER');
            $table->timestamp('notificado_en')->nullable();
            $table->timestamp('leido_en')->nullable();
            $table->timestamp('archivado_en')->nullable();
            $table->string('codigo_referencia', 50)->nullable();
            $table->string('codigo_expediente', 50)->nullable();
            $table->timestamps();

            $table->index(['casilla_id', 'registrado_en']);
            $table->index('codigo_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
