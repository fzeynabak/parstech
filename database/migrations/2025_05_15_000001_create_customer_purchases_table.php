<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerPurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('customer_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('invoice_id');
            $table->double('total_amount')->default(0);
            $table->timestamp('purchase_date');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('persons')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_purchases');
    }
}
