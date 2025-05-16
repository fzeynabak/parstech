<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonsTable extends Migration
{
    public function up()
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->string('nickname')->nullable();
            $table->string('accounting_code')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->string('price_list')->nullable();
            $table->string('tax_type')->nullable();
            $table->string('national_code')->nullable();
            $table->string('economic_code')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('branch_code')->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('fax')->nullable();
            $table->string('phone1')->nullable();
            $table->string('phone2')->nullable();
            $table->string('phone3')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('marriage_date')->nullable();
            $table->date('join_date')->nullable();
            $table->timestamp('last_purchase_at')->nullable();
            $table->bigInteger('total_purchases')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('persons');
    }
}
