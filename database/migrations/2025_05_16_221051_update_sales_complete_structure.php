<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            // اضافه کردن فیلدهای جدید اگر وجود ندارند
            if (!Schema::hasColumn('sales', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0)->after('total_price');
            }
            if (!Schema::hasColumn('sales', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('sales', 'remaining_amount')) {
                $table->decimal('remaining_amount', 15, 2)->default(0)->after('paid_amount');
            }
            if (!Schema::hasColumn('sales', 'discount_type')) {
                $table->enum('discount_type', ['percentage', 'fixed'])->nullable()->after('discount');
            }
            if (!Schema::hasColumn('sales', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('tax');
            }
            if (!Schema::hasColumn('sales', 'shipping_cost')) {
                $table->decimal('shipping_cost', 12, 2)->default(0)->after('tax_rate');
            }
            if (!Schema::hasColumn('sales', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('shipping_cost');
            }
            if (!Schema::hasColumn('sales', 'expected_delivery_date')) {
                $table->date('expected_delivery_date')->nullable()->after('shipping_address');
            }
            if (!Schema::hasColumn('sales', 'actual_delivery_date')) {
                $table->date('actual_delivery_date')->nullable()->after('expected_delivery_date');
            }
            if (!Schema::hasColumn('sales', 'payment_terms')) {
                $table->string('payment_terms')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('sales', 'notes')) {
                $table->text('notes')->nullable()->after('payment_terms');
            }
            if (!Schema::hasColumn('sales', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('sales', 'tags')) {
                $table->json('tags')->nullable()->after('internal_notes');
            }
            if (!Schema::hasColumn('sales', 'currency_rate')) {
                $table->decimal('currency_rate', 10, 4)->default(1)->after('currency_id');
            }
        });

        // به‌روزرسانی رکوردهای موجود
        DB::statement('UPDATE sales SET
            total_amount = COALESCE(total_price, 0) - COALESCE(discount, 0) + COALESCE(tax, 0),
            remaining_amount = COALESCE(total_price, 0) - COALESCE(discount, 0) + COALESCE(tax, 0) - COALESCE(paid_amount, 0)
        ');
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'total_amount',
                'paid_amount',
                'remaining_amount',
                'discount_type',
                'tax_rate',
                'shipping_cost',
                'shipping_address',
                'expected_delivery_date',
                'actual_delivery_date',
                'payment_terms',
                'notes',
                'internal_notes',
                'tags',
                'currency_rate'
            ]);
        });
    }
};
