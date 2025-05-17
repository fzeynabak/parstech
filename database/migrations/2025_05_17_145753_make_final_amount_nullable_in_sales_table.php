<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('final_amount', 20, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            // اگر قبلا NOT NULL بوده اینجا باید مقدار default بزنی یا NOT NULL کنی
            $table->decimal('final_amount', 20, 2)->default(0)->change();
        });
    }
};
