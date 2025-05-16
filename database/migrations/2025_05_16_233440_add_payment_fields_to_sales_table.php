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
        Schema::table('sales', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'paid_amount')) $table->bigInteger('paid_amount')->default(0);
            if (!Schema::hasColumn('sales', 'final_amount')) $table->bigInteger('final_amount')->default(0);
            if (!Schema::hasColumn('sales', 'remaining_amount')) $table->bigInteger('remaining_amount')->default(0);
            if (!Schema::hasColumn('sales', 'cash_amount')) $table->bigInteger('cash_amount')->default(0)->nullable();
            if (!Schema::hasColumn('sales', 'card_amount')) $table->bigInteger('card_amount')->default(0)->nullable();
            if (!Schema::hasColumn('sales', 'cheque_amount')) $table->bigInteger('cheque_amount')->default(0)->nullable();
            if (!Schema::hasColumn('sales', 'pos_amount')) $table->bigInteger('pos_amount')->default(0)->nullable();
            if (!Schema::hasColumn('sales', 'online_amount')) $table->bigInteger('online_amount')->default(0)->nullable();
            // فیلدهای دیگر مشابه بالا...
        });
    }
};
