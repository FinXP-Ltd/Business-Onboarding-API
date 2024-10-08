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
        Schema::table('natural_person_addresses', function (Blueprint $table) {
            $table->string('city')->nullable()->after('country_bidx');
            $table->text('city_bidx')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('natural_person_addresses', function (Blueprint $table) {
            $table->dropColumn('city');
            $table->dropColumn('city_bidx');
        });
    }
};
