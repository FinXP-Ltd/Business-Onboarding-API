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
        Schema::create('company_representative_identity_information', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('company_representative_id');

            $table->unsignedInteger('index')->nullable(true);
            $table->unsignedInteger('order')->nullable(true);
            $table->string('id_type')->nullable(true);
            $table->string('country_of_issue')->nullable(true);
            $table->string('id_number')->nullable(true);
            $table->string('document_date_issued')->nullable(true);
            $table->string('document_expired_date')->nullable(true);
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
        Schema::dropIfExists('company_representative_identity_information');
    }
};
