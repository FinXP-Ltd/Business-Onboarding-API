<?php

namespace database\migrations;

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
        Schema::create('kycp_fields', function (Blueprint $table) {
            $table->id();
            $table->integer('program_id');
            $table->integer('entity_id');
            $table->string('key');
            $table->string('type')->nullable();
            $table->string('bp_table')->nullable();
            $table->string('lookup_id')->nullable();
            $table->boolean('repeater')->default(false);
            $table->boolean('required')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kycp_fields');
    }
};
