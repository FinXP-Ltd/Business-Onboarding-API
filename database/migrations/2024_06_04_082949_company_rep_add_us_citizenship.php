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
        Schema::table('company_representative_identity_information', function (Blueprint $table) {
           $table->string('us_citizenship')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_representative_identity_information', function (Blueprint $table) {
            $table->dropColumn('us_citizenship');
        });
    }
};
