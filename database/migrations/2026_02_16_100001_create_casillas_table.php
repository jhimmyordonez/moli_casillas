<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casillas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('usuario_auth_id')->unique();
            $table->string('tipo_documento', 10);
            $table->string('numero_documento', 20);
            $table->string('nombre_mostrar');
            $table->string('email')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casillas');
    }
};
