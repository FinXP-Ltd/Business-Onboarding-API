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
        Schema::table('client_invites', function (Blueprint $table) {
            $table->renameColumn('agent_id', 'parent_id');
            $table->foreignUuid('head_parent_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_invites', function (Blueprint $table) {
            $table->renameColumn('parent_id', 'agent_id');
            $table->dropColumn('head_parent_id');
        });
    }
};
