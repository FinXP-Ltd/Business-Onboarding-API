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
        Schema::create('lookups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('lookup_type_id')->nullable(false);
            $table->foreign('lookup_type_id')->references('id')->on('lookup_types');

            $table->unsignedBigInteger('lookuptable_id');
            $table->string('lookuptable_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lookups');
    }
};
