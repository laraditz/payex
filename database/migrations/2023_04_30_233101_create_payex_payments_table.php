<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payex_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ref_no', 100)->nullable();
            $table->string('currency_code', 5)->nullable();
            $table->bigInteger('amount')->nullable();
            $table->string('status', 5)->nullable();
            $table->string('status_description')->nullable();
            $table->string('payment_status', 5)->nullable();
            $table->string('payment_description')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_no')->nullable();
            $table->text('description')->nullable();
            $table->string('return_url')->nullable();
            $table->string('callback_url')->nullable();
            $table->json('metadata')->nullable();
            $table->json('response')->nullable();
            $table->json('callback_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payex_payments');
    }
};
