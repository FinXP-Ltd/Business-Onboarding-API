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
        Schema::create('iban4u_payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignUuid('business_id')->nullable(false);

            $table->string('purpose_of_account_opening', 128)->nullable();
            $table->string('partners_incoming_transactions')->nullable();
            $table->string('partners_outgoing_transactions')->nullable();
            $table->integer('estimated_monthly_transactions')->nullable();
            $table->double('average_amount_transaction_euro')->nullable();
            $table->enum('accepting_third_party_funds', ['YES', 'NO'])->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iban4u_payment_accounts');
    }
};
