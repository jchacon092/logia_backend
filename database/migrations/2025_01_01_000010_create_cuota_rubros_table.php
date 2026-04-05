<?php
// database/migrations/2025_01_01_000010_create_cuota_rubros_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cuota_rubros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuota_id')->constrained('cuotas')->cascadeOnDelete();
            $table->foreignId('rubro_id')->constrained('rubros')->cascadeOnDelete();
            $table->decimal('monto', 12, 2)->default(0);
            $table->boolean('checked')->default(false);
            $table->string('nota', 250)->nullable();
            $table->timestamps();

            $table->unique(['cuota_id','rubro_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('cuota_rubros');
    }
};
