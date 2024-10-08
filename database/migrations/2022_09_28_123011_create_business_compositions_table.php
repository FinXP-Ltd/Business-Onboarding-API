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
        Schema::create('business_compositions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignUuid('business_id')->nullable(false);

            $table->enum('model_type', ['P', 'N'])->nullable(false);
            $table->unsignedSmallInteger('voting_share')->nullable();
            $table->date('start_date')->nullable(false);
            $table->date('end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_compositions');
    }
};
