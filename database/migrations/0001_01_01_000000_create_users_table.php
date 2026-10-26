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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', [
                'admin',
                'supervisor',
                'encargado_activos',
                'encargado_prestamos',
                'tecnico',
                'usuario',
            ])->default('usuario')->comment('Cargos: admin, supervisor, encargado de activos, encargado de prestamos, tecnico, usuario');
            $table->enum('user_type', [
                'alumno',
                'docente',
                'administrativo',
            ])->default('alumno')->comment('Tipo institucional: solo alumnos y maestros de la universidad');
            $table->string('institutional_id')->unique()->comment('Matricula o clave de empleado universitario');
            $table->string('career_or_department')->nullable()->comment('Carrera, facultad o departamento academico');
            $table->string('phone', 25)->nullable();
            $table->enum('status', ['activo', 'inactivo', 'sancionado'])->default('activo');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
