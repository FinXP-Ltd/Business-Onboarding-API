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
        Schema::create('ac_credit_card_processing_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('business_id');
            $table->string('proof_of_ownership_of_the_domain')->nullable(true);
            $table->string('proof_of_ownership_of_the_domain_size')->nullable(true);
            $table->string('processing_history')->nullable(true);
            $table->string('processing_history_size')->nullable(true);
            $table->string('copy_of_bank_settlement')->nullable(true);
            $table->string('copy_of_bank_settlement_size')->nullable(true);
            $table->string('company_pci_certificate')->nullable(true);
            $table->string('company_pci_certificate_size')->nullable(true);
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
        Schema::dropIfExists('ac_credit_card_processing_documents');
    }
};
