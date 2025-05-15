<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('currency_id');
            $table->string('title')->nullable();
            $table->dateTime('issued_at');
            $table->decimal('total_price', 20, 2)->default(0);
            $table->timestamps();

            // اگر روابط کلیدی لازم است (اختیاری)
            // $table->foreign('customer_id')->references('id')->on('customers');
            // $table->foreign('seller_id')->references('id')->on('sellers');
            // $table->foreign('currency_id')->references('id')->on('currencies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
