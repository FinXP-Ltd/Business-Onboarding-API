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
        Schema::create('company_iban4u_accounts', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('company_information_id');
            $table->integer('annual_turnover')->nullable();
            $table->string('purpose_of_account_opening')->nullable();

            $table->string('deposit_type')->nullable();
            $table->string('deposit_approximate_per_month')->nullable();
            $table->string('deposit_cumulative_per_month')->nullable();

            $table->string('withdrawal_type')->nullable();
            $table->string('withdrawal_approximate_per_month')->nullable();
            $table->string('withdrawal_cumulative_per_month')->nullable();

            $table->string('held_accounts')->nullable();
            $table->string('held_accounts_description')->nullable();
            $table->string('refused_banking_relationship')->nullable();
            $table->string('refused_banking_relationship_description')->nullable();
            $table->string('terminated_banking_relationship')->nullable();
            $table->string('terminated_banking_relationship_description')->nullable();

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
        Schema::dropIfExists('company_iban4u_accounts');
    }
};
