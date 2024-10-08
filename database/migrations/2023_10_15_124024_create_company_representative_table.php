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
        Schema::create('company_representative', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('business_id');
            $table->unsignedInteger('index')->nullable(true);
            $table->unsignedInteger('order')->nullable(true);
            $table->string('first_name')->nullable(true);
            $table->string('middle_name')->nullable(true);
            $table->string('surname')->nullable(true);
            $table->string('date_of_birth')->nullable(true);
            $table->string('place_of_birth')->nullable(true);
            $table->string('nationality')->nullable(true);
            $table->string('citizenship')->nullable(true);
            $table->string('email_address')->nullable(true);
            $table->string('phone_code')->nullable(true);
            $table->string('phone_number')->nullable(true);
            $table->string('roles_in_company')->nullable(true);
            $table->string('percent_ownership')->nullable(true);
            $table->string('iban4u_rights')->nullable(true);
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
        Schema::dropIfExists('company_representative');
    }
};
