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
        Schema::create('cc_processing_countries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_card_processing_id');
            $table->foreign('credit_card_processing_id')->references('id')->on('credit_card_processings');          
            $table->string('countries_where_product_offered')->nullable(true);
            $table->string('distribution_per_country')->nullable(true);
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
        Schema::dropIfExists('cc_processing_countries');
    }
};
