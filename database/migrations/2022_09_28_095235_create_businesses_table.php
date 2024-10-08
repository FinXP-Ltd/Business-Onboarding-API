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
        Schema::create('businesses', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();

            $table->date('foundation_date')->nullable();
            $table->string('vat_number', 15)->nullable();
            $table->string('telephone', 15)->nullable();
            $table->string('email', 64)->nullable();
            $table->string('website', 64)->nullable();
            $table->longText('additional_website')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('businesses');
    }
};
