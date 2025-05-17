<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('service_code')->unique();
            $table->unsignedBigInteger('service_category_id')->nullable();
            $table->string('unit');
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('tax', 5, 2)->default(0);
            $table->decimal('execution_cost', 15, 2)->default(0);
            $table->string('short_description', 1000)->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_vat_included')->default(true);
            $table->boolean('is_discountable')->default(true);
            $table->timestamps();

            $table->foreign('service_category_id')->references('id')->on('service_categories')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
};
