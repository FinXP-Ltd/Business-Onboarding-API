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
        Schema::create('natural_persons', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('title', 5);
            $table->string('name');
            $table->string('surname', 70);
            $table->string('sex', 22);
            $table->string('date_of_birth');
            $table->string('place_of_birth');
            $table->string('email_address');
            $table->string('country_code', 5);
            $table->string('mobile');
            $table->unique(['name', 'surname', 'date_of_birth']);
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
        Schema::dropIfExists('natural_persons');
    }
};
