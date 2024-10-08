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
        Schema::create('senior_officer_residential_address', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('senior_officer_id');

            $table->string('street_number')->nullable(true);
            $table->string('street_name')->nullable(true);
            $table->string('postal_code')->nullable(true);
            $table->string('city')->nullable(true);
            $table->string('country')->nullable(true);
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
       Schema::dropIfExists('senior_officer_residential_address');
    }
};
