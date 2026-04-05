<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('egresos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('categoria_egreso_id')
                  ->constrained('categorias_egreso')
                  ->restrictOnDelete();

            $table->string('descripcion');
            $table->decimal('monto', 12, 2);
            $table->date('fecha');

            // Folio, número de factura, referencia externa, etc.
            $table->string('referencia')->nullable();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('egresos');
    }
};
