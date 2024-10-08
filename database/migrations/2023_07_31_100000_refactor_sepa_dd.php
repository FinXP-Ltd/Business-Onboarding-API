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
      $table->boolean('currently_processing_sepa_dd')->change();
      $table->dropColumn('details_of_product_services');
      $table->dropColumn('sepa_dd_value');
    });

    Schema::create('sepa_dd', function (Blueprint $table) {
      $table->id();
      $table->timestamps();

      $table->foreignUuid('sepa_dd_direct_debits')->nullable(false);

      $table->string('name')->nullable(false);
      $table->string('value')->nullable(false);
      $table->string('description')->nullable(false);
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
      $table->string('currently_processing_sepa_dd', 7)->nullable(false);
      $table->string('details_of_product_services');
      $table->string('sepa_dd_value');
    });
    Schema::drop('sepa_dd');
  }
};
