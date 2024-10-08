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
        Schema::table('company_sepa_dd', function (Blueprint $table) {
            $table->string('expected_global_mon_vol')->change();
        });
    }

    public function down()
    {
        Schema::table('company_sepa_dd', function (Blueprint $table) {
            $table->double('expected_global_mon_vol')->change();
        });
    }
};
