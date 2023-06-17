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
        Schema::table('payex_payments', function (Blueprint $table) {
            $table->string('txn_id')->nullable()->after('ref_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payex_payments', function (Blueprint $table) {
            $table->dropColumn('txn_id');
        });
    }
};
