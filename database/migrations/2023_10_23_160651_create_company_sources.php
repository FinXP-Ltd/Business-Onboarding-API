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
        Schema::create('company_sources', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('company_information_id');
            $table->string('type')->nullable();
            $table->string('source_name')->nullable();
            $table->string('other_value')->nullable();
            $table->boolean('is_selected')->default(false);
            $table->timestamps();

            $table->index('type');
            $table->index('other_value');
            $table->index('source_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('source_of_wealths');
    }
};
