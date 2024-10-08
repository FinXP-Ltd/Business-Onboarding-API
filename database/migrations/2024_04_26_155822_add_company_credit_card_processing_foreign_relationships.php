<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tableNames = [
        'company_cc_trading_url',
        'company_cc_country'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexesFound = $sm->listTableIndexes('company_credit_card_processing');

        if(!array_key_exists('primary', $indexesFound)) {
            Schema::table('company_credit_card_processing', function (Blueprint $table) {
                $table->uuid('id')->primary()->change();
            });
        }

        foreach($this->tableNames as $key) {
            Schema::table($key, function (Blueprint $table) {
                $table->foreignUuid('company_credit_card_processing_id')
                    ->change()
                    ->references('id')
                    ->on('company_credit_card_processing')
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
        Schema::table('company_credit_card_processing', function (Blueprint $table) {
            $table->dropIndex(['id']);
        });

        foreach($this->tableNames as $key) {
            Schema::table($key, function (Blueprint $table) {
                $table->dropForeign(['company_credit_card_processing_id']);
            });
        }
    }
};
