<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_number', 50)->unique();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('currency_id');
            $table->string('title')->nullable();
            $table->dateTime('issued_at');
            $table->decimal('total_price', 20, 2)->default(0);
            $table->timestamps();

            // اگر جدول های persons، sellers، currencies را داری این Foreign Key ها را نگه دار، اگر نه کامنت کن
            $table->foreign('customer_id')->references('id')->on('persons')->onDelete('restrict');
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('restrict');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
