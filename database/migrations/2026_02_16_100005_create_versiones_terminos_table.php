<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versiones_terminos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('version', 20);
            $table->text('contenido_html');
            $table->boolean('es_activo')->default(false);
            $table->timestamp('publicado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versiones_terminos');
    }
};
