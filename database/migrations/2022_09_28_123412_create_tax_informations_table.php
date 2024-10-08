<?php

use App\Models\Business;
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
        Schema::create('tax_informations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignUuid('business_id')->nullable(false);

            $table->string('name', 48)->nullable(false);
            $table->string('tax_country', 3)->nullable(false);
            $table->string('registration_number', 20)->nullable(false)->unique();
            $table->enum('registration_type', Business::REGISTRATION_TYPE)->nullable();
            $table->string('tax_identification_number', 20)->nullable();

            $table->unique(['name', 'tax_country', 'registration_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_informations');
    }
};
