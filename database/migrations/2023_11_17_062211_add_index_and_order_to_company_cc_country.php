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
        Schema::table('company_cc_country', function (Blueprint $table) {
            $table->unsignedInteger('index')->nullable(true)->after('company_credit_card_processing_id');
            $table->unsignedInteger('order')->nullable(true)->after('index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_cc_country', function (Blueprint $table) {
            $table->dropColumn('index');
            $table->dropColumn('order');
        });
    }
};
