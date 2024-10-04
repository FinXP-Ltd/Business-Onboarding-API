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
        Schema::create('senior_management_officer_documents', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('senior_officer_id');

            $table->string('proof_of_address')->nullable(true);
            $table->string('proof_of_address_size')->nullable(true);
            $table->string('identity_document')->nullable(true);
            $table->string('identity_document_size')->nullable(true);
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
        Schema::dropIfExists('senior_management_officer_documents');
    }
};
