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
        Schema::create('shared_applications', function (Blueprint $table) {
            $table->foreignUuid('user_id');
            $table->foreignUuid('agent_id');
            $table->foreignUuid('business_id');

            $table->unique(['user_id', 'agent_id', 'business_id']);

            // $table->foreign('user_id')
            //     ->references('id')
            //     ->on('users')
            //     ->cascadeOnDelete();

            // $table->foreign('agent_id')
            //     ->references('id')
            //     ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shared_applications');
    }
};
