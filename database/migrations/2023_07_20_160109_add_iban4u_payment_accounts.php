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
        Schema::table('iban4u_payment_accounts', function (Blueprint $table) {
            $table->string('share_capital')->nullable();
            $table->integer('annual_turnover')->nullable();

            $table->string('deposit_trading')->nullable();
            $table->string('deposit_approximate_per_month')->nullable();
            $table->string('deposit_cumulative_per_month')->nullable();

            $table->string('withdrawal_trading')->nullable();
            $table->string('withdrawal_approximate_per_month')->nullable();
            $table->string('withdrawal_cumulative_per_month')->nullable();

            $table->string('held_accounts')->nullable();
            $table->string('held_accounts_description')->nullable();
            $table->string('refused_banking_relationship')->nullable();
            $table->string('refused_banking_relationship_description')->nullable();
            $table->string('terminated_banking_relationship')->nullable();
            $table->string('terminated_banking_relationship_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('iban4u_payment_accounts', function (Blueprint $table) {
            $table->dropColumn('share_capital');
            $table->dropColumn('annual_turnover');

            $table->dropColumn('deposit_trading');
            $table->dropColumn('deposit_countries');
            $table->dropColumn('deposit_approximate_per_month');
            $table->dropColumn('deposit_cumulative_per_month');

            $table->dropColumn('withdrawal_trading');
            $table->dropColumn('withdrawal_countries');
            $table->dropColumn('withdrawal_approximate_per_month');
            $table->dropColumn('withdrawal_cumulative_per_month');

            $table->dropColumn('held_accounts');
            $table->dropColumn('held_accounts_description');
            $table->dropColumn('refused_banking_relationship');
            $table->dropColumn('refused_banking_relationship_description');
            $table->dropColumn('terminated_banking_relationship');
            $table->dropColumn('terminated_banking_relationship_description');
        });
    }
};