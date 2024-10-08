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
            $table->string('distribution_per_country')->change();
        });
    }

    public function down()
    {
        Schema::table('company_cc_country', function (Blueprint $table) {
            $table->double('distribution_per_country')->change();
        });
    }
};
