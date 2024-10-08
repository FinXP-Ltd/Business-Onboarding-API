<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Entity;
use App\Enums\EntityTypes;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kycp_requirements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignUuid('document_id');

            $table->enum('entity', Entity::values())->nullable(false);
            $table->enum('entity_type', EntityTypes::names())->nullable(false);
            $table->enum('document_type', config('kycp-requirement.document_types'))->nullable(false);
            $table->integer('kycp_key')->nullable(false);
            $table->boolean('required')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kycp_requirements');
    }
};
