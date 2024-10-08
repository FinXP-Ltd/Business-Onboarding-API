<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tableNames = [
        'business_compositions',
        'business_details',
        'business_products',
        'company_information',
        'contact_details',
        'credit_card_processings',
        'iban4u_payment_accounts',
        'indicias',
        'political_person_entity',
        'sepa_dd_direct_debits',
        'tax_informations',
        'shared_applications'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach($this->tableNames as $key) {
            Schema::table($key, function (Blueprint $table) {
                $table->foreignUuid('business_id')
                    ->change()
                    ->references('id')
                    ->on('businesses')
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
        foreach($this->tableNames as $key) {
            Schema::table($key, function (Blueprint $table) {
                $table->dropForeign(['business_id']);
            });
        }
    }
};
