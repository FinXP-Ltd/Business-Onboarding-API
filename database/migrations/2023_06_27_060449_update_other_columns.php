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
        Schema::table('tax_informations', function (Blueprint $table) {
            $table->string('jurisdiction', 3)->nullable();
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->string('trading_name')->nullable();
        });

        Schema::table('business_details', function (Blueprint $table) {
            $table->string('description')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_email')->nullable();
            $table->string('is_part_of_group')->nullable();
            $table->string('parent_holding_company')->nullable();
            $table->string('parent_holding_company_other')->nullable();
            $table->string('has_fiduciary_capacity')->nullable();
            $table->string('has_constituting_documents')->nullable();
            $table->string('is_company_licensed')->nullable();
            $table->string('license_rep_juris')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tax_informations', function (Blueprint $table) {
            $table->string('jurisdiction')->dropColumn();
        });
        Schema::table('business_details', function (Blueprint $table) {
            $table->string('trading_name')->dropColumn();
            $table->string('description')->dropColumn();
            $table->string('contact_person_name')->dropColumn();
            $table->string('contact_person_email')->dropColumn();
            $table->string('email')->dropColumn();
            $table->string('website')->dropColumn();
            $table->string('additional_website')->dropColumn();
            $table->string('is_part_of_group')->dropColumn();
            $table->string('parent_holding_company')->dropColumn();
            $table->string('parent_holding_company_other')->dropColumn();
            $table->string('has_fiduciary_capacity')->dropColumn();
            $table->string('has_constituting_documents')->dropColumn();
            $table->string('is_company_licensed')->dropColumn();
            $table->string('license_rep_juris')->nullable(false)->change();
        });
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('trading_name')->dropColumn();
        });
    }
};
