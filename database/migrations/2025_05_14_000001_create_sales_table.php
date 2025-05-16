<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('title')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->double('total_price')->default(0);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('persons')->nullOnDelete();
            $table->foreign('seller_id')->references('id')->on('sellers')->nullOnDelete();
            $table->foreign('currency_id')->references('id')->on('currencies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
