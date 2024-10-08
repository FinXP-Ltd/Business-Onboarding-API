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
        Schema::create('company_information', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('business_id');

            $table->string('company_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('type_of_company')->nullable();
            $table->string('company_trading_as')->nullable();
            $table->date('date_of_incorporation')->nullable();
            $table->string('country_of_incorporation')->nullable();
            $table->string('number_of_employees')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('tin')->nullable();
            $table->string('tin_jurisdiction')->nullable();
            $table->string('industry_type')->nullable();
            $table->string('industry_description')->nullable();
            $table->string('share_capital')->nullable();
            $table->string('previous_year_turnover')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('additional_website')->nullable();
            $table->string('is_group_corporate')->nullable();
            $table->string('parent_holding_company')->nullable();
            $table->string('parent_holding_company_other')->nullable();
            $table->string('company_fiduciary_capacity')->nullable();
            $table->string('allow_constituting_documents')->nullable();
            $table->string('is_company_licensed')->nullable();
            $table->string('licensed_in')->nullable();
            $table->string('full_name')->nullable();
            $table->string('email_address')->nullable();
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
        Schema::dropIfExists('company_information');
    }
};
