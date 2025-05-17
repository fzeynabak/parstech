<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->string('service_code')->unique()->nullable();
            $table->unsignedBigInteger('service_category_id')->nullable();
            $table->string('unit')->nullable();
            $table->bigInteger('price')->nullable();
            $table->integer('tax')->nullable();
            $table->bigInteger('execution_cost')->nullable();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_vat_included')->default(1);
            $table->boolean('is_discountable')->default(1);
            $table->timestamps();

            // اگر جدول service_categories داری:
            // $table->foreign('service_category_id')->references('id')->on('service_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
