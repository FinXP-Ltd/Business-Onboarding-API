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
        Schema::create('company_representative_documents', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('company_representative_id');

            $table->unsignedInteger('index')->nullable(true);
            $table->unsignedInteger('order')->nullable(true);
            $table->string('proof_of_address')->nullable(true);
            $table->string('proof_of_address_size')->nullable(true);
            $table->string('identity_document')->nullable(true);
            $table->string('identity_document_size')->nullable(true);
            $table->string('source_of_wealth')->nullable(true);
            $table->string('source_of_wealth_size')->nullable(true);
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
        Schema::dropIfExists('company_representative_documents');
    }
};
