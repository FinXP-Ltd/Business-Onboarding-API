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
        Schema::create('company_addresses', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('company_information_id');

            $table->string('registered_street_number')->nullable();
            $table->string('registered_street_name')->nullable();
            $table->string('registered_postal_code')->nullable();
            $table->string('registered_city')->nullable();
            $table->string('registered_country')->nullable();
            $table->string('operational_street_number')->nullable();
            $table->string('operational_street_name')->nullable();
            $table->string('operational_postal_code')->nullable();
            $table->string('operational_city')->nullable();
            $table->string('operational_country')->nullable();

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
        Schema::dropIfExists('company_addresses');
    }
};
