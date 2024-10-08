<?php

use App\Models\Document;
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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignUuid('documentable_id');
            $table->string('documentable_type', 50);

            $table->enum('owner_type', Document::OWNER_TYPES)->nullable(false);
            $table->enum('document_type', Document::DOCUMENT_TYPES);
            $table->string('file_name', 48);
            $table->string('file_type', 24);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
