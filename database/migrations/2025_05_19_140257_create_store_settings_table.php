<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->default('پارس تک');
            $table->string('store_description')->nullable();
            $table->string('store_address')->nullable();
            $table->string('store_phone')->nullable();
            $table->string('store_mobile')->nullable();
            $table->string('store_email')->nullable();
            $table->string('store_website')->nullable();
            $table->string('invoice_header_text')->nullable();
            $table->string('invoice_footer_text')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('stamp_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->decimal('default_tax_rate', 5, 2)->default(9.00);
            $table->boolean('show_qr_code')->default(true);
            $table->json('social_media')->nullable();
            $table->json('bank_accounts')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_settings');
    }
}
