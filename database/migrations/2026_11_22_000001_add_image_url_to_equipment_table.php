<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el campo de imagen al catálogo de equipos.
     * Permite al personal subir una URL de imagen del producto para el portal público.
     */
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('specifications')
                  ->comment('URL de imagen del equipo para el catálogo público (opcional)');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
    }
};
