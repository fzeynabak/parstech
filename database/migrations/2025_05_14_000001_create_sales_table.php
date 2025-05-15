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
            $table->string('invoice_number')->unique();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('currency_id');
            $table->string('title')->nullable();
            $table->date('issued_at');
            $table->date('due_at');
            $table->bigInteger('total_price')->default(0);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('persons');
            $table->foreign('seller_id')->references('id')->on('sellers');
            $table->foreign('currency_id')->references('id')->on('currencies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
