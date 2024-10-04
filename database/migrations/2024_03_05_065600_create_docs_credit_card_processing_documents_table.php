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
        Schema::create('docs_credit_card_processing_documents', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('company_information_id');
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable(); //memorandum_and_articles_of_association
            $table->string('file_size')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('update_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('docs_credit_card_processing_documents');
    }
};
