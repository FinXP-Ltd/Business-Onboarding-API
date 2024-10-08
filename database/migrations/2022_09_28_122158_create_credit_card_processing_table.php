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
        Schema::create('credit_card_processings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignUuid('business_id')->nullable(false);

            $table->string('currently_processing_cc_payments', 7)->nullable(false);
            $table->mediumText('trading_urls')->nullable();
            $table->string('offer_recurring_billing', 7)->nullable(false);
            $table->string('recurring_details', 128)->nullable();
            $table->string('offer_refunds', 7)->nullable(false);
            $table->string('refund_details', 128)->nullable();
            $table->string('country', 3)->nullable();
            $table->float('distribution_sale_volume')->nullable();
            $table->string('processing_account_primary_currency', 4)->nullable();
            $table->double('average_ticket_amount')->nullable();
            $table->double('highest_ticket_amount')->nullable();
            $table->string('other_alternative_payment_methods')->nullable();
            $table->string('other_alternative_payment_method_used')->nullable();
            $table->string('current_mcc', 15)->nullable();
            $table->string('current_descriptor', 20)->nullable();
            $table->double('cb_volumes_twelve_months')->nullable();
            $table->double('cc_volumes_twelve_months')->nullable();
            $table->integer('refund_volumes_twelve_months')->nullable();
            $table->string('current_acquire_psp', 15)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_card_processings');
    }
};
