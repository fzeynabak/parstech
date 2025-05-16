<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'completed', 'cancelled'])
                  ->default('pending')
                  ->after('total_price');
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->string('payment_method')->nullable()->after('paid_at');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->text('cancellation_reason')->nullable()->after('payment_reference');
            $table->decimal('discount', 12, 2)->default(0)->after('total_price');
            $table->decimal('tax', 12, 2)->default(0)->after('discount');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'paid_at',
                'payment_method',
                'payment_reference',
                'cancellation_reason',
                'discount',
                'tax'
            ]);
        });
    }
};
