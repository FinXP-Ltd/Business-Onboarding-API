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
        Schema::create('sepa_dd_direct_debits', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignUuid('business_id')->nullable(false);

            $table->string('currently_processing_sepa_dd', 7)->nullable(false);
            $table->string('details_of_product_services', 128)->nullable();
            $table->double('sepa_dd_value')->nullable();
            $table->double('sepa_dd_volume_per_month')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sepa_dd_direct_debits');
    }
};
