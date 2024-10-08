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
        Schema::create('natural_person_identification_docs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('natural_person_id');
            $table->string('document_type', 15);
            $table->mediumText('document_number');
            $table->string('document_country_of_issue', 3);
            $table->date('document_expiry_date');
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
        Schema::dropIfExists('natural_person_identification_docs');
    }
};
