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
        Schema::create('company_cc_country', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('company_credit_card_processing_id')->nullable();
            $table->string('countries_where_product_offered')->nullable(true);
            $table->double('distribution_per_country')->nullable(true);
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
        Schema::dropIfExists('company_cc_country');
    }
};
