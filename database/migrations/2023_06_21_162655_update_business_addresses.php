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
        Schema::table('business_addresses', function (Blueprint $table) {
            $table->string('line_1', 96)->nullable()->change();
            $table->string('line_2', 96)->nullable()->change();
            $table->string('postal_code', 12)->nullable()->change();
            $table->mediumText('city')->nullable()->change();
            $table->string('country', 56)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_addresses', function (Blueprint $table) {
            $table->string('line_1', 96)->nullable(false)->change();
            $table->string('line_2', 96)->nullable()->change();
            $table->string('postal_code', 12)->nullable(false)->change();
            $table->mediumText('city')->nullable(false)->change();
            $table->string('country', 3)->nullable(false)->change();
        });
    }
};
