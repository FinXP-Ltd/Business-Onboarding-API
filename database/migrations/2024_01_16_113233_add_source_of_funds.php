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
        Schema::table('company_information', function (Blueprint $table) {
            $table->string('source_of_funds')->nullable(true)->after('email_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_information', function (Blueprint $table) {
            $table->dropColumn('source_of_funds');
        });
    }
};
