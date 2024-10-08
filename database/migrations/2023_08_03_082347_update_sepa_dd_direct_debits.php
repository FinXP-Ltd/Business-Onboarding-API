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
          $table->boolean('currently_processing_sepa_dd')->nullable(true)->change();
        });
        Schema::table('sepa_dd', function (Blueprint $table) {
          $table->foreignUuid('sepa_dd_direct_debits')->nullable(true)->change();
          $table->string('name')->nullable(true)->change();
          $table->string('value')->nullable(true)->change();
          $table->string('description')->nullable(true)->change();
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
          $table->boolean('currently_processing_sepa_dd')->nullable(false)->change();
        });
        Schema::table('sepa_dd', function (Blueprint $table) {
          $table->foreignUuid('sepa_dd_direct_debits')->nullable(false)->change();
          $table->string('name')->nullable(false)->change();
          $table->string('value')->nullable(false)->change();
          $table->string('description')->nullable(false)->change();
        });
    }
};
