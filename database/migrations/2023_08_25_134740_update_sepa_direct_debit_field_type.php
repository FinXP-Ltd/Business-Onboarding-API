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
          $table->uuid('id')->change();
          $table->string('currently_processing_sepa_dd')->nullable(true)->change();
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
          $table->id()->change();
          $table->boolean('currently_processing_sepa_dd')->nullable(false)->change();
        });
    }
};
