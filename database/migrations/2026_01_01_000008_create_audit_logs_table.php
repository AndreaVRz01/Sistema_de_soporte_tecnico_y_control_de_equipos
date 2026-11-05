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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_folio', 40)->unique()->comment('Identificador único del evento forense');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Operador que ejecutó la acción');
            $table->string('entity_type', 60)->comment('Entidad afectada: Equipment, Loan, Incident, User, Sanction');
            $table->unsignedBigInteger('entity_id')->nullable()->comment('ID de la entidad afectada');
            $table->string('action', 60)->comment('Acción ejecutada: CREAR, ACTUALIZAR, BAJA, PRESTAR, DEVOLVER, LOGIN');
            $table->string('ip_address', 45)->nullable()->comment('Dirección IP de origen');
            $table->text('user_agent')->nullable()->comment('Cliente / Navegador del operador');
            $table->text('description')->comment('Resumen descriptivo del movimiento');
            $table->json('diff_payload')->nullable()->comment('Diferencial de cambios antes y después');
            $table->enum('risk_level', ['normal', 'advertencia', 'critico'])->default('normal')->comment('Nivel de riesgo operativo');
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['created_at', 'risk_level']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

