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
        Schema::create('loan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $table->enum('condition_on_loan', ['excelente', 'bueno', 'regular'])->default('bueno')->comment('Condicion fisica al entregar');
            $table->enum('condition_on_return', ['excelente', 'bueno', 'regular', 'dañado', 'incompleto'])->nullable()->comment('Condicion fisica al devolver');
            $table->string('accessories_included')->nullable()->comment('Accesorios entregados: cargador, cables, estuche, control');
            $table->text('notes_on_loan')->nullable();
            $table->text('notes_on_return')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();

            $table->unique(['loan_id', 'equipment_id']);
            $table->index('equipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_items');
    }
};
