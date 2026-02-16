<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjuntos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mensaje_id')->constrained('mensajes')->cascadeOnDelete();
            $table->string('nombre_archivo');
            $table->string('tipo_mime', 100);
            $table->unsignedBigInteger('tamano_bytes');
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('driver_almacenamiento', 20)->default('local');
            $table->string('ruta_almacenamiento');
            $table->timestamp('subido_en');

            $table->index('mensaje_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjuntos');
    }
};
