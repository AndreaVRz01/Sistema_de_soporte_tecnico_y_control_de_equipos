<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('status', 30)->default('disponible')->change();
            $table->string('functional_status', 32)->default('funcional')->after('status');
            $table->date('warranty_until')->nullable()->after('acquisition_date');
            $table->text('warranty_details')->nullable()->after('warranty_until');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->boolean('requires_supervisor_approval')->default(false)->after('status');
            $table->foreignId('supervisor_approved_by_id')->nullable()->after('authorized_by_id')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('supervisor_approved_at')->nullable()->after('supervisor_approved_by_id');
        });
    }

    public function down(): void
    {
        DB::table('equipment')->whereIn('status', ['mantenimiento', 'en_reparacion'])
            ->update(['status' => 'en_mantenimiento']);

        Schema::table('loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supervisor_approved_by_id');
            $table->dropColumn(['requires_supervisor_approval', 'supervisor_approved_at']);
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['functional_status', 'warranty_until', 'warranty_details']);
            $table->enum('status', [
                'disponible',
                'prestado',
                'en_mantenimiento',
                'baja',
                'reservado',
            ])->default('disponible')->change();
        });
    }
};