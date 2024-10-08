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
        Schema::table('business_compositionable', function (Blueprint $table) {
            $table->string('uid')->nullable();
            $table->string('application_id')->nullable();
            $table->integer('entity_id')->nullable();
            $table->integer('entity_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_compositionable', function (Blueprint $table) {
            $table->dropColumn('uid');
            $table->dropColumn('application_id');
            $table->dropColumn('entity_id');
            $table->dropColumn('entity_type_id');
        });
    }
};
