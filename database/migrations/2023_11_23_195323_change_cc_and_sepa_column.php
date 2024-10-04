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
        Schema::table('ac_credit_card_processing_documents', function (Blueprint $table) {
            $table->renameColumn('copy_of_bank_settlement', 'cc_copy_of_bank_settlement');
            $table->renameColumn('copy_of_bank_settlement_size', 'cc_copy_of_bank_settlement_size');
        });

        Schema::table('ac_sepa_direct_debit_documents', function (Blueprint $table) {
            $table->renameColumn('copy_of_bank_settlement', 'sepa_copy_of_bank_settlement');
            $table->renameColumn('copy_of_bank_settlement_size', 'sepa_copy_of_bank_settlement_size');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
