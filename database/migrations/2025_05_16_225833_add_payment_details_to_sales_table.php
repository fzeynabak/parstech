<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentDetailsToSalesTable extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            // فیلدهای مربوط به پرداخت
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('payment_status')->default('pending');

            // فیلدهای مربوط به پرداخت نقدی
            $table->decimal('cash_amount', 15, 2)->nullable();
            $table->string('cash_reference')->nullable();
            $table->timestamp('cash_paid_at')->nullable();

            // فیلدهای مربوط به کارت به کارت
            $table->decimal('card_amount', 15, 2)->nullable();
            $table->string('card_reference')->nullable();
            $table->string('card_number')->nullable();
            $table->string('card_bank')->nullable();
            $table->timestamp('card_paid_at')->nullable();

            // فیلدهای مربوط به دستگاه کارتخوان
            $table->decimal('pos_amount', 15, 2)->nullable();
            $table->string('pos_reference')->nullable();
            $table->string('pos_terminal')->nullable();
            $table->timestamp('pos_paid_at')->nullable();

            // فیلدهای مربوط به پرداخت آنلاین
            $table->decimal('online_amount', 15, 2)->nullable();
            $table->string('online_reference')->nullable();
            $table->string('online_transaction_id')->nullable();
            $table->timestamp('online_paid_at')->nullable();

            // فیلدهای مربوط به چک
            $table->decimal('cheque_amount', 15, 2)->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('cheque_bank')->nullable();
            $table->date('cheque_due_date')->nullable();
            $table->string('cheque_status')->nullable();
            $table->timestamp('cheque_received_at')->nullable();

            // یادداشت‌های پرداخت
            $table->text('payment_notes')->nullable();
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'total_amount',
                'paid_amount',
                'remaining_amount',
                'payment_status',
                'cash_amount',
                'cash_reference',
                'cash_paid_at',
                'card_amount',
                'card_reference',
                'card_number',
                'card_bank',
                'card_paid_at',
                'pos_amount',
                'pos_reference',
                'pos_terminal',
                'pos_paid_at',
                'online_amount',
                'online_reference',
                'online_transaction_id',
                'online_paid_at',
                'cheque_amount',
                'cheque_number',
                'cheque_bank',
                'cheque_due_date',
                'cheque_status',
                'cheque_received_at',
                'payment_notes'
            ]);
        });
    }
}
