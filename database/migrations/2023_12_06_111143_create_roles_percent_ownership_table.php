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
    Schema::create('roles_percent_ownership', function (Blueprint $table) {
        $table->uuid('id');
        $table->foreignUuid('company_representative_id')->nullable();
        $table->string('roles_in_company')->nullable(true);
        $table->string('percent_ownership')->nullable(true);
        $table->unsignedInteger('index')->nullable(true);
        $table->unsignedInteger('order')->nullable(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles_percent_ownership');
    }
};
