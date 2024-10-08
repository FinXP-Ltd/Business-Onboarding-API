<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tableNames = [
        'company_addresses',
        'company_source_countries',
        'company_sources',
        'company_sepa_dd',
        'company_iban4u_accounts',
        'company_representative',
        'data_protection_marketings',
        'declarations',
        'ac_iban4u_documents',
        'ac_general_documents',
        'ac_credit_card_processing_documents',
        'ac_sepa_direct_debit_documents',
        'senior_management_officer',
        'usa_tax_liability',
        'company_credit_card_processing',
        'docs_general_documents',
        'docs_iban4u_documents',
        'docs_credit_card_processing_documents',
        'docs_sepa_direct_debit_documents',
        'pending_applications'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexesFound = $sm->listTableIndexes('company_information');

        if(!array_key_exists('primary', $indexesFound)) {
            Schema::table('company_information', function (Blueprint $table) {
                $table->uuid('id')->primary()->change();
            });
        }

        foreach($this->tableNames as $key) {
            Schema::table($key, function (Blueprint $table) use ($key) {

                $explodedKey = explode('_', $key);

                $newKey = '';

                foreach($explodedKey as $key => $value) {
                    $newKey .= substr($value, 0, 4);
                }

                $table->foreign('company_information_id', "{$newKey}_company_info")
                    ->change()
                    ->references('id')
                    ->on('company_information')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_information', function (Blueprint $table) {
            $table->dropIndex(['id']);
        });

        foreach($this->tableNames as $key) {
            Schema::table($key, function (Blueprint $table) {
                $table->dropForeign(['company_information_id']);
            });
        }
    }
};
