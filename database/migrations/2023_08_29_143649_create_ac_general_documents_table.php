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
        Schema::create('ac_general_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('business_id');
            $table->string('memorandum_and_articles_of_association')->nullable(true);
            $table->string('memorandum_and_articles_of_association_size')->nullable(true);
            $table->string('certificate_of_incorporation')->nullable(true);
            $table->string('certificate_of_incorporation_size')->nullable(true);
            $table->string('registry_exact')->nullable(true);
            $table->string('registry_exact_size')->nullable(true);
            $table->string('company_structure_chart')->nullable(true);
            $table->string('company_structure_chart_size')->nullable(true);
            $table->string('proof_of_address_document')->nullable(true);
            $table->string('proof_of_address_document_size')->nullable(true);
            $table->string('operating_license')->nullable(true);
            $table->string('operating_license_size')->nullable(true);
            
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
        Schema::dropIfExists('ac_required_documents');
    }
};
