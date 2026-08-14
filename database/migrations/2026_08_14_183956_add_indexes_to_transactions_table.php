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
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['merchant_id', 'created_at'], 'idx_merchant_created');
            $table->index(['merchant_id', 'outlet_id', 'created_at'], 'idx_merchant_outlet_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_merchant_created');
            $table->dropIndex('idx_merchant_outlet_created');
        });
    }
};
