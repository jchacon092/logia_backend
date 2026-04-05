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
Schema::create('trazados', function (Blueprint $table) {
    $table->id();
    $table->string('titulo');
    $table->date('fecha');
    $table->string('ponente'); // persona que dará el trazado
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trazados');
    }
};
