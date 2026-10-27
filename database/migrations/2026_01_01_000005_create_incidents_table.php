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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 30)->unique()->comment('Codigo de ticket, ej. INC-2026-0001');
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('loan_item_id')->nullable()->constrained('loan_items')->nullOnDelete()->comment('Prestamo relacionado si el incidente ocurrio durante uso');
            $table->foreignId('reported_by_id')->comment('Usuario que detecto y reporto la falla')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_technician_id')->nullable()->comment('Tecnico de soporte asignado')->constrained('users')->nullOnDelete();
            $table->string('title', 150)->comment('Titulo o resumen del fallo');
            $table->text('description')->comment('Descripcion detallada del problema o daño');
            $table->enum('type', [
                'hardware',
                'software',
                'daño_fisico',
                'desgaste_natural',
                'perdida_accesorio',
                'mantenimiento_preventivo',
            ])->default('hardware')->comment('Tipo o clasificacion del incidente');
            $table->enum('priority', ['baja', 'media', 'alta', 'critica'])->default('media');
            $table->enum('status', [
                'reportado',
                'asignado',
                'en_diagnostico',
                'en_reparacion',
                'resuelto',
                'baja_definitiva',
                'cancelado',
            ])->default('reportado')->comment('Estado de atencion tecnica');
            $table->text('diagnostic')->nullable()->comment('Diagnostico emitido por el tecnico');
            $table->text('solution_applied')->nullable()->comment('Detalle de reparacion o solucion efectuada');
            $table->decimal('estimated_cost', 10, 2)->nullable()->comment('Costo estimado si requiere repuestos');
            $table->decimal('actual_cost', 10, 2)->nullable()->comment('Costo real de reparacion');
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'assigned_technician_id']);
            $table->index('equipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
