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
        Schema::table('cc_processing_countries', function (Blueprint $table) {
            $table->string('distribution_per_country')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cc_processing_countries', function (Blueprint $table) {
            $table->unsignedBigInteger('distribution_per_country')->nullable(true);
        });
    }
};
