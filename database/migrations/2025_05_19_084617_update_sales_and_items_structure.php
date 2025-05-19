<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // بک‌آپ گرفتن از داده‌های موجود
        $sales = DB::table('sales')->get();
        $saleItems = DB::table('sale_items')->get();

        // حذف کلیدهای خارجی
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
        });

        // به‌روزرسانی جدول sales
        Schema::table('sales', function (Blueprint $table) {
            // حذف ستون‌های اضافی
            $table->dropColumn([
                'reference',
                'currency_id',
                'title',
                'payment_method',
                'payment_reference',
                'payment_notes',
                'cancellation_reason',
                'issued_at',
                'online_amount',
                'online_reference',
                'online_transaction_id',
                'online_paid_at',
                'cheque_status',
                'cheque_received_at',
                'card_bank',
                'pos_terminal',
                'paid_at'
            ]);

            // اضافه کردن ستون‌های جدید
            $table->string('payment_status')->default('unpaid')->after('status');
            $table->text('description')->nullable()->after('cheque_due_date');
            $table->softDeletes();
        });

        // به‌روزرسانی جدول sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            // تغییر نوع داده‌ها از double به decimal
            $table->decimal('unit_price', 15, 2)->change();
            $table->decimal('discount', 15, 2)->default(0)->change();
            $table->decimal('tax', 15, 2)->default(0)->change();
            $table->decimal('total', 15, 2)->change();
        });

        // بازگرداندن مقادیر payment_status
        foreach ($sales as $sale) {
            $paymentStatus = 'unpaid';
            if ($sale->paid_amount >= $sale->final_amount) {
                $paymentStatus = 'paid';
            } elseif ($sale->paid_amount > 0) {
                $paymentStatus = 'partial';
            }

            DB::table('sales')
                ->where('id', $sale->id)
                ->update(['payment_status' => $paymentStatus]);
        }
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            // اضافه کردن مجدد ستون‌های حذف شده
            $table->string('reference')->nullable();
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('restrict');
            $table->string('title')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('payment_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->decimal('online_amount', 15, 2)->nullable();
            $table->string('online_reference')->nullable();
            $table->string('online_transaction_id')->nullable();
            $table->timestamp('online_paid_at')->nullable();
            $table->string('cheque_status')->nullable();
            $table->timestamp('cheque_received_at')->nullable();
            $table->string('card_bank')->nullable();
            $table->string('pos_terminal')->nullable();
            $table->timestamp('paid_at')->nullable();

            // حذف ستون‌های جدید
            $table->dropColumn(['payment_status', 'description', 'deleted_at']);
        });

        // برگرداندن نوع داده‌های جدول sale_items به حالت قبل
        Schema::table('sale_items', function (Blueprint $table) {
            $table->double('unit_price')->change();
            $table->double('discount')->nullable()->change();
            $table->double('tax')->nullable()->change();
            $table->double('total')->nullable()->change();
        });
    }
};
