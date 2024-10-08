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
        Schema::create('pending_applications', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('company_information_id');
            $table->string('company_name')->nullable(true);
            $table->string('status')->nullable(true);
            $table->string('company_trading_as')->nullable(true);
            $table->string('tax_name')->nullable(true);
            $table->boolean('is_same_address')->nullable(true);
            $table->longText('company_products')->nullable(true);
            $table->longText('company_details')->nullable(true);
            $table->longText('company_address')->nullable(true);
            $table->longText('company_sources')->nullable(true);
            $table->longText('sepa_direct_debit')->nullable(true);
            $table->longText('iban4u_payment_account')->nullable(true);
            $table->longText('acquiring_services')->nullable(true);
            $table->longText('company_representatives')->nullable(true);
            $table->longText('declaration_agreement')->nullable(true);
            $table->longText('senior_management_officer')->nullable(true);
            $table->longText('data_protection_and_marketing')->nullable(true);
            $table->longText('data_protection_marketing')->nullable(true);
            $table->longText('required_documents')->nullable(true);
            $table->longText('entities')->nullable(true);
            $table->longText('indicias')->nullable(true);
            $table->longText('declaration')->nullable(true);
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
        Schema::dropIfExists('pending_applications');
    }
};
