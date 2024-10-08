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
        Schema::table('roles_percent_ownership', function (Blueprint $table) {
            $table->string('iban4u_rights')->nullable(true)->after('roles_in_company');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles_percent_ownership', function (Blueprint $table) {
            $table->dropColumn('iban4u_rights');
        });
    }
};
