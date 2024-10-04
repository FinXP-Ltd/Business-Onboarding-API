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
        Schema::create('data_protection_marketings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignUuid('business_id');
            $table->boolean('data_protection_notice')->nullable(true);
            $table->string('receive_messages_from_finxp')->nullable(true);
            $table->string('receive_market_research_survey')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_protection_marketings');
    }
};
