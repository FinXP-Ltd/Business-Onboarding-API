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
        Schema::create('non_natural_person_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('non_natural_person_id');

            $table->mediumText('line_1');
            $table->mediumText('line_2');
            $table->mediumText('postal_code');
            $table->mediumText('locality');
            $table->mediumText('country', 3);
            $table->enum('licensed_reputable_jurisdiction', ['YES', 'NO', 'LICENSE_NOT_REQUIRED'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('non_natural_person_addresses');
    }
};
