<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('miembros', function (Blueprint $table) {
            // Datos adicionales del miembro
            $table->string('direccion')->nullable()->after('telefono');
            $table->string('dpi', 20)->nullable()->after('direccion');
            $table->enum('estado_civil', [
                'soltero', 'casado', 'divorciado', 'viudo', 'otro'
            ])->nullable()->after('dpi');
            $table->date('fecha_ingreso')->nullable()->after('estado_civil');
            $table->enum('estado', [
                'activo', 'suspendido', 'retirado', 'fallecido'
            ])->default('activo')->after('fecha_ingreso');
            $table->string('motivo_baja')->nullable()->after('estado');
            $table->string('foto')->nullable()->after('motivo_baja');

            // Vínculo opcional con un usuario del sistema
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('foto')
                  ->constrained('users')
                  ->nullOnDelete();

            // Soft delete
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('miembros', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropSoftDeletes();
            $table->dropColumn([
                'direccion', 'dpi', 'estado_civil',
                'fecha_ingreso', 'estado', 'motivo_baja', 'foto',
            ]);
        });
    }
};
