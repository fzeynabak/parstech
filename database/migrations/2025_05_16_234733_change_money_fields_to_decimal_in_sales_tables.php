<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMoneyFieldsToDecimalInSalesTables extends Migration
{
    public function up()
    {
        // جدول sales
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('total_price', 20, 2)->change();
            $table->decimal('discount', 20, 2)->change();
            $table->decimal('tax', 20, 2)->change();
            $table->decimal('final_amount', 20, 2)->change();
            $table->decimal('paid_amount', 20, 2)->default(0)->change();
            $table->decimal('remaining_amount', 20, 2)->default(0)->change();

            $table->decimal('cash_amount', 20, 2)->nullable()->default(0)->change();
            $table->decimal('card_amount', 20, 2)->nullable()->default(0)->change();
            $table->decimal('pos_amount', 20, 2)->nullable()->default(0)->change();
            $table->decimal('online_amount', 20, 2)->nullable()->default(0)->change();
            $table->decimal('cheque_amount', 20, 2)->nullable()->default(0)->change();
        });

        // جدول sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('unit_price', 20, 2)->change();
            $table->decimal('discount', 20, 2)->change();
            $table->decimal('tax', 20, 2)->change();
            $table->decimal('total', 20, 2)->change();
        });
    }

    public function down()
    {
        // اگر لازم شد برگردان به عدد صحیح
        Schema::table('sales', function (Blueprint $table) {
            $table->bigInteger('total_price')->change();
            $table->bigInteger('discount')->change();
            $table->bigInteger('tax')->change();
            $table->bigInteger('final_amount')->change();
            $table->bigInteger('paid_amount')->change();
            $table->bigInteger('remaining_amount')->change();

            $table->bigInteger('cash_amount')->nullable()->change();
            $table->bigInteger('card_amount')->nullable()->change();
            $table->bigInteger('pos_amount')->nullable()->change();
            $table->bigInteger('online_amount')->nullable()->change();
            $table->bigInteger('cheque_amount')->nullable()->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->bigInteger('unit_price')->change();
            $table->bigInteger('discount')->change();
            $table->bigInteger('tax')->change();
            $table->bigInteger('total')->change();
        });
    }
}
