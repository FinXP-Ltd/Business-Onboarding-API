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
        Schema::create('ac_iban4u_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('business_id');
            $table->string('agreements_with_the_entities')->nullable(true);
            $table->string('agreements_with_the_entities_size')->nullable(true);
            $table->string('board_resolution')->nullable(true);
            $table->string('board_resolution_size')->nullable(true);
            $table->string('third_party_questionnaire')->nullable(true);
            $table->string('third_party_questionnaire_size')->nullable(true);
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
        Schema::dropIfExists('ac_iban4u_documents');
    }
};
