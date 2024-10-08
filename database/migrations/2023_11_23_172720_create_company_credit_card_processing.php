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

        try {
            $isExists = Schema::hasTable('company_credit_card_processing');
            if (! $isExists) {

                Schema::create('company_credit_card_processing', function (Blueprint $table) {
                    $table->uuid('id');
                    $table->foreignUuid('company_information_id');
                    $table->string('currently_processing_cc_payments')->nullable();
                    $table->string('offer_recurring_billing')->nullable();
                    $table->string('frequency_offer_billing')->nullable();
                    $table->string('if_other_offer_billing')->nullable();
                    $table->string('offer_refunds')->nullable();
                    $table->string('frequency_offer_refunds')->nullable();
                    $table->string('if_other_offer_refunds')->nullable();
                    $table->string('processing_account_primary_currency')->nullable();
                    $table->string('average_ticket_amount')->nullable();
                    $table->string('highest_ticket_amount')->nullable();
                    $table->string('alternative_payment_methods')->nullable();
                    $table->string('payment_method_currently_offered')->nullable();
                    $table->string('current_mcc')->nullable();
                    $table->string('current_descriptor')->nullable();
                    $table->string('cb_volumes_twelve_months')->nullable();
                    $table->string('sales_volumes_twelve_months')->nullable();
                    $table->string('refund_twelve_months')->nullable();
                    $table->string('current_acquire_psp')->nullable();
                    $table->timestamps();
                });
            }
        } catch (Throwable $err) {
            info($err);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_credit_card_processing');
    }
};
