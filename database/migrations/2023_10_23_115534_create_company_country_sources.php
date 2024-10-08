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
        Schema::create('company_source_countries', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('company_information_id');
            $table->string('type')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_selected')->default(false);
            $table->timestamps();

            $table->index('type');
            $table->index('country');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('source_fund_countries');
    }
};
