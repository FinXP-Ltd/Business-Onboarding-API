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
            $table->dropColumn('deposit_trading')->change();
            $table->dropColumn('withdrawal_trading')->change();
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
            $table->string('deposit_trading')->nullable();
            $table->string('withdrawal_trading')->nullable();
        });
    }
};
