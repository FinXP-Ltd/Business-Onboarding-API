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
        Schema::table('tax_informations', function (Blueprint $table) {
            $table->string('tax_country', 3)->nullable()->change();
            $table->string('registration_number', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tax_informations', function (Blueprint $table) {
            $table->string('tax_country', 3)->nullable(false)->change();
            $table->string('registration_number', 20)->nullable(false)->change();
        });
    }
};
