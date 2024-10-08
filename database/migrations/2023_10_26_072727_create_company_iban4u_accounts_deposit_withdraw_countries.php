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
        Schema::create('company_iban4u_accounts_deposit_withdraw_countries', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('company_iban4u_account_id');
            $table->string('type')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_selected')->default(false);
            $table->timestamps();

            $table->index('type');
            $table->index('country');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_iban4u_accounts_deposit_withdraw_countries');
    }
};
