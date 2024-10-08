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
        Schema::create('ac_sepa_direct_debit_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('business_id');
            $table->string('template_of_customer_mandate')->nullable(true);
            $table->string('template_of_customer_mandate_size')->nullable(true);
            $table->string('processing_history_with_chargeback_and_ratios')->nullable(true);
            $table->string('processing_history_with_chargeback_and_ratios_size')->nullable(true);
            $table->string('copy_of_bank_settlement')->nullable(true);
            $table->string('copy_of_bank_settlement_size')->nullable(true);
            $table->string('product_marketing_information')->nullable(true);
            $table->string('product_marketing_information_size')->nullable(true);
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
        Schema::dropIfExists('ac_sepa_direct_debit_documents');
    }
};
