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
        Schema::create('natural_person_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('natural_person_id');
            $table->mediumText('line_1');
            $table->mediumText('line_2');
            $table->mediumText('locality');
            $table->mediumText('postal_code');
            $table->string('country');
            $table->string('nationality');
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
        Schema::dropIfExists('natural_person_addresses');
    }
};
