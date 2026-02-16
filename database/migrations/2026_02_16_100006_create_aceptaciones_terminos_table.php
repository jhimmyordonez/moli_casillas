<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aceptaciones_terminos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('version_terminos_id')->constrained('versiones_terminos')->cascadeOnDelete();
            $table->uuid('usuario_auth_id');
            $table->timestamp('aceptado_en');
            $table->string('ip', 45)->nullable();

            $table->index(['usuario_auth_id', 'version_terminos_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aceptaciones_terminos');
    }
};
