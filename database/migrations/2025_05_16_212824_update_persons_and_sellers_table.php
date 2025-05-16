<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('persons', function (Blueprint $table) {
            if (!Schema::hasColumn('persons', 'full_name')) {
                $table->string('full_name')->nullable()->virtualAs(
                    "CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))"
                );
            }
        });

        Schema::table('sellers', function (Blueprint $table) {
            if (!Schema::hasColumn('sellers', 'full_name')) {
                $table->string('full_name')->nullable()->virtualAs(
                    "CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))"
                );
            }
        });
    }

    public function down()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });
    }
};
