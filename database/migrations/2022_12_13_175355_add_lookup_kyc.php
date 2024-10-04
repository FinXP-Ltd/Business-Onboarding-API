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
        Schema::table('lookup_types', function (Blueprint $table) {
            $table->integer('lookup_id')->nullable();
            $table->integer('lookup_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lookup_types', function (Blueprint $table) {
            $table->dropColumn('lookup_id');
            $table->dropColumn('lookup_type_id');
        });
    }
};
