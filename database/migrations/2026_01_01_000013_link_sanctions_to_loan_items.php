<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sanctions', function (Blueprint $table) {
            $table->foreignId('loan_item_id')->nullable()->after('loan_id')
                ->constrained('loan_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sanctions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('loan_item_id');
        });
    }
};