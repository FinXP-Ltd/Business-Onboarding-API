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
        Schema::create('business_details', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignUuid('business_id');

            $table->string('business_purpose', 128)->nullable();
            $table->unsignedInteger('number_employees')->nullable();
            $table->unsignedFloat('share_capital', 12)->nullable(false);
            $table->unsignedInteger('number_shareholder')->nullable(false);
            $table->unsignedInteger('number_directors')->nullable();
            $table->string('previous_year_turnover', 4)->nullable();
            $table->string('license_rep_juris', 11)->nullable(false);
            $table->unsignedInteger('business_year_count')->nullable();
            $table->string('source_of_funds', 128)->nullable(false);
            $table->boolean('terms_and_conditions')->nullable(false);
            $table->boolean('privacy_accepted')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_details');
    }
};
