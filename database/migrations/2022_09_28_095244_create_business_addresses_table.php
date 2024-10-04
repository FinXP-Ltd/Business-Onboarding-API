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
        Schema::create('business_addresses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignUuid('business_id')->nullable(false);

            $table->string('lookup_type_id')->nullable(false);
            $table->string('line_1', 96)->nullable(false);
            $table->string('line_2', 96)->nullable();
            $table->string('postal_code', 12)->nullable(false);
            $table->mediumText('city')->nullable(false);
            $table->string('country', 3)->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_addresses');
    }
};
