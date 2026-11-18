<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('institutional_id')->nullable()->change();
            $table->string('registration_status', 20)->default('approved')->index();
            $table->foreignId('registration_reviewed_by_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('registration_reviewed_at')->nullable();
        });
    }

    public function down(): void
    {
        DB::table('users')
            ->whereNull('institutional_id')
            ->orderBy('id')
            ->get(['id'])
            ->each(function (object $user): void {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['institutional_id' => 'DOC-SIN-MATRICULA-' . $user->id]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registration_reviewed_by_id');
            $table->dropIndex(['registration_status']);
            $table->dropColumn(['registration_status', 'registration_reviewed_at']);
            $table->string('institutional_id')->nullable(false)->change();
        });
    }
};
