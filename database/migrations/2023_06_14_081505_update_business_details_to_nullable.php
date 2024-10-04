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
        Schema::table('business_details', function (Blueprint $table) {
            $table->unsignedFloat('share_capital', 12)->nullable()->change();
            $table->unsignedInteger('number_shareholder')->nullable()->change();
            $table->boolean('terms_and_conditions')->nullable()->change();
            $table->boolean('privacy_accepted')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_details', function (Blueprint $table) {
            $table->unsignedFloat('share_capital', 12)->nullable(false)->change();
            $table->unsignedInteger('number_shareholder')->nullable(false)->change();
             $table->boolean('terms_and_conditions')->nullable(false)->change();
             $table->boolean('privacy_accepted')->nullable(false)->chaneg();
        });
    }
};
