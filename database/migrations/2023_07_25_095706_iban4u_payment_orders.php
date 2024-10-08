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
        Schema::create('iban4u_payment_orders', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('iban4u_payment_accounts_id');
            $table->string('name')->nullable();
            $table->string('country')->nullable();
            $table->enum('type',['incoming', 'outgoing'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iban4u_payment_orders');
    }
};
