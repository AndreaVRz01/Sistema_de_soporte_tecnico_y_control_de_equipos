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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 30)->unique()->comment('Folio unico de prestamo, ej. PRE-2026-0001');
            $table->foreignId('user_id')->comment('Alumno o Docente solicitante')->constrained('users')->restrictOnDelete();
            $table->foreignId('authorized_by_id')->nullable()->comment('Encargado de prestamos que autoriza')->constrained('users')->nullOnDelete();
            $table->foreignId('dispatched_by_id')->nullable()->comment('Encargado que entrega fisicamente')->constrained('users')->nullOnDelete();
            $table->foreignId('received_by_id')->nullable()->comment('Encargado que recibe la devolucion')->constrained('users')->nullOnDelete();
            $table->text('purpose')->comment('Motivo del prestamo: clase, taller, evaluacion, etc.');
            $table->string('location_of_use', 120)->nullable()->comment('Aula, laboratorio o espacio universitario de uso');
            $table->timestamp('request_date')->useCurrent()->comment('Fecha y hora en que se solicita');
            $table->timestamp('loan_date')->nullable()->comment('Fecha y hora de entrega fisica');
            $table->timestamp('expected_return_date')->comment('Fecha y hora limite de devolucion acordada');
            $table->timestamp('actual_return_date')->nullable()->comment('Fecha y hora real en que se devolvio');
            $table->enum('status', [
                'solicitado',
                'aprobado',
                'rechazado',
                'entregado',
                'devuelto',
                'devuelto_con_observacion',
                'vencido',
                'cancelado',
            ])->default('solicitado')->comment('Estado del ciclo de vida del prestamo');
            $table->text('rejection_reason')->nullable()->comment('Motivo en caso de denegar');
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->index(['status', 'user_id']);
            $table->index('expected_return_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
