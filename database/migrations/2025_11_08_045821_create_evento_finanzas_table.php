<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('evento_finanzas', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->date('fecha');
    $table->decimal('total_proyectado',12,2)->default(0);
    $table->decimal('total_neto',12,2)->default(0);
    $table->json('gastos_detalle')->nullable(); // [{item, costo}]
    $table->decimal('restante',12,2)->default(0);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento_finanzas');
    }
};
