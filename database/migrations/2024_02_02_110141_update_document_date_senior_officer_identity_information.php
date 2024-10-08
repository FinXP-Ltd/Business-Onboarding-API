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
        Schema::table('senior_officer_identity_information', function (Blueprint $table) {
            $table->string('document_date_issued')->change();
            $table->string('document_expired_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('senior_officer_identity_information', function (Blueprint $table) {
            $table->date('document_date_issued')->change();
            $table->date('document_expired_date')->change();
        });
    }
};
