<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'final_amount')) {
                $table->bigInteger('final_amount')->default(0)->after('tax');
            }
            if (!Schema::hasColumn('sales', 'remaining_amount')) {
                $table->bigInteger('remaining_amount')->default(0)->after('final_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'final_amount')) {
                $table->dropColumn('final_amount');
            }
            if (Schema::hasColumn('sales', 'remaining_amount')) {
                $table->dropColumn('remaining_amount');
            }
        });
    }
};
