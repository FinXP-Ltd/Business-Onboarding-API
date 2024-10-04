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
        Schema::table('shared_applications', function (Blueprint $table) {
            $table->renameColumn('agent_id', 'parent_id');
            $table->unique(['user_id', 'parent_id', 'business_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shared_applications', function (Blueprint $table) {
            $table->renameColumn('parent_id', 'agent_id');
            $table->unique(['user_id', 'agent_id', 'business_id']);
        });
    }
};
