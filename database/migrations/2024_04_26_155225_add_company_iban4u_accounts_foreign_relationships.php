<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tableNames = [
        'company_iban4u_accounts_deposit_withdraw_countries',
        'company_iban4u_accounts_activities'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexesFound = $sm->listTableIndexes('company_iban4u_accounts');

        if(!array_key_exists('primary', $indexesFound)) {
            Schema::table('company_iban4u_accounts', function (Blueprint $table) {
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

                $table->foreign('company_iban4u_account_id', "{$newKey}_company_iban4u_accounts")
                    ->change()
                    ->references('id')
                    ->on('company_iban4u_accounts')
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
        Schema::table('company_iban4u_accounts', function (Blueprint $table) {
            $table->dropIndex(['id']);
        });

        foreach($this->tableNames as $key) {
            Schema::table($key, function (Blueprint $table) {
                $table->dropForeign(['company_iban4u_account_id']);
            });
        }
    }
};
