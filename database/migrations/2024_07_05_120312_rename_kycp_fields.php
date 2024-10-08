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
        Schema::table('kycp_fields', function ($table) {
            $table->renameColumn('bp_table', 'mapping_table');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kycp_fields', function ($table) {
            $table->renameColumn('mapping_table', 'bp_table');
        });
    }
};
