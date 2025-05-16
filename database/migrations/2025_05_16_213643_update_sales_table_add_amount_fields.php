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
                $table->decimal('total_amount', 12, 2)->default(0)->after('total_price');
            }
            if (!Schema::hasColumn('sales', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('sales', 'remaining_amount')) {
                $table->decimal('remaining_amount', 12, 2)->default(0)->after('paid_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['total_amount', 'paid_amount', 'remaining_amount']);
        });
    }
};
