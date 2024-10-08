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
        Schema::create('company_iban4u_accounts_activities', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('company_iban4u_account_id');
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('country')->nullable();
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
        Schema::dropIfExists('company_iban4u_accounts_activities');
    }
};
