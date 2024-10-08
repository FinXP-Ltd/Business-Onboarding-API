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
        Schema::create('contact_details', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignUuid('business_id');

            $table->string('first_name', 36)->nullable(false);
            $table->string('last_name', 36)->nullable(false);
            $table->string('position_held', 36)->nullable(false);
            $table->string('country_code', 3)->nullable(false);
            $table->string('mobile_no', 15)->nullable(false);
            $table->string('email', 64)->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contact_details');
    }
};
