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
        Schema::create('senior_officer_identity_information', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('senior_officer_id');

            $table->string('id_type')->nullable(true);
            $table->string('country_of_issue')->nullable(true);
            $table->string('id_number')->nullable(true);
            $table->date('document_date_issued')->nullable(true);
            $table->date('document_expired_date')->nullable(true);
            $table->string('high_net_worth')->nullable(true);
            $table->string('politically_exposed_person')->nullable(true);
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
        Schema::dropIfExists('senior_officer_identity_information');
    }
};
