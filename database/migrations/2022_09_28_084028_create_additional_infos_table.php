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
        Schema::create('additional_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('natural_person_id');
            $table->mediumText('occupation');
            $table->mediumText('employment');
            $table->mediumText('position');
            $table->mediumText('source_of_income');
            $table->mediumText('source_of_wealth');
            $table->mediumText('source_of_wealth_details');
            $table->mediumText('other_source_of_wealth_details');
            $table->boolean('pep');
            $table->boolean('us_citizenship');
            $table->mediumText('tin');
            $table->string('country_tax', 3);
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
        Schema::dropIfExists('additional_infos');
    }
};
