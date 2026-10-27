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
        Schema::create('sanctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('Alumno o Docente sancionado')->constrained('users')->restrictOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained('loans')->nullOnDelete();
            $table->foreignId('incident_id')->nullable()->constrained('incidents')->nullOnDelete();
            $table->foreignId('applied_by_id')->comment('Admin o Supervisor que aplica la sancion')->constrained('users')->restrictOnDelete();
            $table->enum('sanction_type', [
                'suspension_temporal',
                'cobro_reparacion',
                'amonestacion_escrita',
                'bloqueo_definitivo',
            ])->default('suspension_temporal');
            $table->text('reason')->comment('Motivo de la sancion: retraso, rotura, perdida');
            $table->date('start_date');
            $table->date('end_date')->nullable()->comment('Fecha de fin de suspension si aplica');
            $table->decimal('amount_to_pay', 10, 2)->nullable()->comment('Monto a pagar si hay reposicion o reparacion');
            $table->boolean('is_paid')->default(false);
            $table->enum('status', ['activa', 'cumplida', 'cancelada'])->default('activa');
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sanctions');
    }
};
