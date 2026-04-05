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
Schema::create('envios_gran_logia', function (Blueprint $table) {
    $table->id();
    $table->date('fecha_envio');
    $table->string('folio')->nullable();
    $table->string('descripcion')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envio_gran_logias');
    }
};
