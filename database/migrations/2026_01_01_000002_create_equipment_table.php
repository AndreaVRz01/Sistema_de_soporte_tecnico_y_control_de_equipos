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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('inventory_code', 50)->unique()->comment('Codigo de activo o patrimonio universitario');
            $table->string('serial_number', 80)->unique()->comment('Numero de serie de fabricante');
            $table->string('name')->comment('Nombre descriptivo del equipo');
            $table->string('brand', 60)->comment('Marca del fabricante');
            $table->string('model', 60)->comment('Modelo del equipo');
            $table->text('specifications')->nullable()->comment('Especificaciones tecnicas y caracteristicas');
            $table->enum('status', [
                'disponible',
                'prestado',
                'en_mantenimiento',
                'baja',
                'reservado',
            ])->default('disponible')->comment('Estado de disponibilidad del activo');
            $table->enum('physical_condition', [
                'excelente',
                'bueno',
                'regular',
                'malo',
            ])->default('excelente')->comment('Condicion fisica y estetica del activo');
            $table->string('location', 100)->default('Almacén Central de TI')->comment('Ubicacion fisica en la universidad');
            $table->date('acquisition_date')->nullable()->comment('Fecha de compra o ingreso al inventario');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
