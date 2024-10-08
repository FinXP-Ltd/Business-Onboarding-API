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
        Schema::table('credit_card_processings', function (Blueprint $table) {
            $table->string('currently_processing_cc_payments', 7)->nullable()->change();
            $table->string('offer_recurring_billing', 7)->nullable()->change();
            $table->string('offer_refunds', 7)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credit_card_processings', function (Blueprint $table) {
            $table->string('currently_processing_cc_payments', 7)->nullable(false)->change();
            $table->string('offer_recurring_billing', 7)->nullable(false)->change();
            $table->string('offer_refunds', 7)->nullable(false)->change();
        });
    }
};
