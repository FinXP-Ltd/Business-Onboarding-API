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
        Schema::create('senior_management_officer', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('business_id');

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
            $table->boolean('required_indicator')->nullable(true);
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
        Schema::dropIfExists('senior_management_officer');
    }
};
