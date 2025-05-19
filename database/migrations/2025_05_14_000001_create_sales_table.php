<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained('persons')->onDelete('restrict');
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('restrict');
            $table->decimal('total_price', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);

            // وضعیت فاکتور
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');

            // جزئیات پرداخت نقدی
            $table->decimal('cash_amount', 15, 2)->default(0);
            $table->string('cash_reference')->nullable();
            $table->timestamp('cash_paid_at')->nullable();

            // جزئیات کارت به کارت
            $table->decimal('card_amount', 15, 2)->default(0);
            $table->string('card_reference')->nullable();
            $table->string('card_number', 16)->nullable();
            $table->timestamp('card_paid_at')->nullable();

            // جزئیات پرداخت POS
            $table->decimal('pos_amount', 15, 2)->default(0);
            $table->string('pos_reference')->nullable();
            $table->timestamp('pos_paid_at')->nullable();

            // جزئیات چک
            $table->decimal('cheque_amount', 15, 2)->default(0);
            $table->string('cheque_number')->nullable();
            $table->string('cheque_bank')->nullable();
            $table->date('cheque_due_date')->nullable();

            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
};
