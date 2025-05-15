<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseFieldsToPersonsTable extends Migration
{
    public function up()
    {
        Schema::table('persons', function (Blueprint $table) {
            if (!Schema::hasColumn('persons', 'last_purchase_at')) {
                $table->timestamp('last_purchase_at')->nullable();
            }
            if (!Schema::hasColumn('persons', 'total_purchases')) {
                $table->bigInteger('total_purchases')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn(['last_purchase_at', 'total_purchases']);
        });
    }
}
