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
        Schema::table('sepa_dd_direct_debits', function (Blueprint $table) {
            $table->string('ac_sepa_dd_volume_per_month')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sepa_dd_direct_debits', function (Blueprint $table) {
            $table->string('ac_sepa_dd_volume_per_month');
        });
    }
};