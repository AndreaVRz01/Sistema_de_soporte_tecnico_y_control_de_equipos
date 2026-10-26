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
        Schema::create('incident_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->foreignId('technician_id')->comment('Tecnico que efectuo o registro el avance')->constrained('users')->restrictOnDelete();
            $table->string('activity', 150)->comment('Accion realizada: revision, reemplazo de pieza, etc.');
            $table->text('notes')->nullable()->comment('Observaciones tecnicas del proceso');
            $table->string('status_before', 40)->nullable();
            $table->string('status_after', 40)->nullable();
            $table->timestamps();

            $table->index('incident_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_logs');
    }
};
