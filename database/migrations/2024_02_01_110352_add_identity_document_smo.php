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
       Schema::table('senior_management_officer_documents', function (Blueprint $table) {
            $table->string('identity_document_addt')->nullable(true);
            $table->string('identity_document_addt_size')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('senior_management_officer_documents', function (Blueprint $table) {
            $table->dropColumn('identity_document_addt');
            $table->dropColumn('identity_document_addt_size');
        });
    }
};
