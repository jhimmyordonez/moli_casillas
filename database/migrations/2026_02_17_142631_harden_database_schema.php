<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Hardening casillas: Composite unique for doc search
        Schema::table('casillas', function (Blueprint $table) {
            $table->unique(['tipo_documento', 'numero_documento'], 'casillas_doc_unique');
        });

        // 2. Hardening mensajes: Indexes for faster listing and searching
        Schema::table('mensajes', function (Blueprint $table) {
            $table->index('asunto');
            $table->index('registrado_en');
        });

        // 3. Hardening adjuntos: checksum obligatory
        Schema::table('adjuntos', function (Blueprint $table) {
            $table->string('checksum_sha256', 64)->nullable(false)->change();
        });

        // 4. Hardening eventos_mensajes: use jsonb for metadata querying (PostgreSQL specific)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE eventos_mensajes ALTER COLUMN metadatos TYPE jsonb USING metadatos::jsonb');
        }

        // 5. Hardening aceptaciones_terminos: unique per version per user
        Schema::table('aceptaciones_terminos', function (Blueprint $table) {
            $table->unique(['version_terminos_id', 'usuario_auth_id'], 'aceptaciones_unique');
        });

        // 6. Hardening versiones_terminos: partial unique index for only one active version
        if (DB::getDriverName() === 'pgsql' || DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX unv_solo_una_version_activa ON versiones_terminos (es_activo) WHERE (es_activo = 1 OR es_activo = true)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('casillas', function (Blueprint $table) {
            $table->dropUnique('casillas_doc_unique');
        });

        Schema::table('mensajes', function (Blueprint $table) {
            $table->dropIndex(['asunto']);
            $table->dropIndex(['registrado_en']);
        });

        Schema::table('adjuntos', function (Blueprint $table) {
            $table->string('checksum_sha256', 64)->nullable()->change();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE eventos_mensajes ALTER COLUMN metadatos TYPE json USING metadatos::json');
        }

        Schema::table('aceptaciones_terminos', function (Blueprint $table) {
            $table->dropUnique('aceptaciones_unique');
        });

        if (DB::getDriverName() === 'pgsql' || DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS unv_solo_una_version_activa');
        }
    }
};
