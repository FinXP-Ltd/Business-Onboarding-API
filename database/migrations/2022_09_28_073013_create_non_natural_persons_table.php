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
        Schema::create('non_natural_persons', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name', 70)->unique();
            $table->string('registration_number', 15)->unique();
            $table->date('date_of_incorporation');
            $table->string('country_of_incorporation', 3);
            $table->string('name_of_shareholder_percent_held');
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
        Schema::dropIfExists('non_natural_persons');
    }
};
