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
            $table->string('frequency_offer_billing')->after('offer_recurring_billing')->nullable(true);
            $table->string('if_other_offer_billing')->after('frequency_offer_billing')->nullable(true);
            $table->string('frequency_offer_refunds')->after('offer_refunds')->nullable(true);
            $table->string('if_other_offer_refunds')->after('frequency_offer_refunds')->nullable(true);
            $table->string('ac_alternative_payment_methods')->nullable(true);
            $table->string('ac_method_currently_offered')->nullable(true);
            $table->string('ac_average_ticket_amount')->nullable(true);
            $table->string('ac_highest_ticket_amount')->nullable(true);
            $table->string('ac_cb_volumes_twelve_months')->nullable(true);
            $table->string('ac_cc_volumes_twelve_months')->nullable(true);
            $table->string('ac_refund_volumes_twelve_months')->nullable(true);
            $table->string('ac_current_mcc')->nullable(true);
            $table->string('ac_current_descriptor')->nullable(true);
            $table->string('ac_current_acquire_psp')->nullable(true);
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
            $table->dropColumn('frequency_offer_billing');
            $table->dropColumn('if_other_offer_billing');
            $table->dropColumn('frequency_offer_refunds');
            $table->dropColumn('if_other_offer_refunds');
            $table->dropColumn('ac_alternative_payment_methods');
            $table->dropColumn('ac_method_currently_offered');
            $table->dropColumn('ac_average_ticket_amount');
            $table->dropColumn('ac_highest_ticket_amount');
            $table->dropColumn('ac_cb_volumes_twelve_months');
            $table->dropColumn('ac_cc_volumes_twelve_months');
            $table->dropColumn('ac_refund_volumes_twelve_months');
            $table->dropColumn('ac_current_mcc');
            $table->dropColumn('ac_current_descriptor');
            $table->dropColumn('ac_current_acquire_psp');
        });
    }
};
