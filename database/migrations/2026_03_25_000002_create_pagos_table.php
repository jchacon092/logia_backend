<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();

            // Quién pagó (nullable: puede ser donación de otra logia, rifa, etc.)
            $table->foreignId('miembro_id')
                  ->nullable()
                  ->constrained('miembros')
                  ->nullOnDelete();

            $table->date('fecha_pago');

            // Rango de meses que cubre este pago
            $table->date('fecha_inicio');  // primer mes cubierto (YYYY-MM-01)
            $table->date('fecha_fin');     // último mes cubierto  (YYYY-MM-01)

            $table->decimal('monto', 12, 2);

            // mensualidad | iniciacion | exaltacion | examen | donacion | rifa | otro
            $table->string('concepto')->default('mensualidad');

            // Descripción libre (ej: "Cuota ene-jun 2026", "Donación Logia Hiram", etc.)
            $table->string('descripcion')->nullable();

            // Número correlativo de recibo por año  (ej: numero=15, anio=2026 → "2026-015")
            $table->unsignedSmallInteger('anio_recibo');
            $table->unsignedInteger('numero_recibo');

            // Usuario que registró el pago
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->unique(['anio_recibo', 'numero_recibo']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('pagos');
    }
};
