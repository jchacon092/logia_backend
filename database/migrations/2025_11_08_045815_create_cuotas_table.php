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
Schema::create('cuotas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('miembro_id')->constrained('miembros')->cascadeOnDelete();
    $table->date('fecha');
    $table->decimal('monto',10,2);
    $table->string('concepto')->default('mensualidad'); // mensualidad, exaltación, etc.
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};
