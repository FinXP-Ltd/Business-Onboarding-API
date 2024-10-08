<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tableNames = [
        'company_representative_residential_address',
        'company_representative_identity_information',
        'company_representative_documents',
        'roles_percent_ownership'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexesFound = $sm->listTableIndexes('company_representative');

        if(!array_key_exists('primary', $indexesFound)) {
            Schema::table('company_representative', function (Blueprint $table) {
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

                $table->foreign('company_representative_id', "{$newKey}_company_representative")
                    ->change()
                    ->references('id')
                    ->on('company_representative')
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
        Schema::table('company_representative', function (Blueprint $table) {
            $table->dropIndex(['id']);
        });

        foreach($this->tableNames as $key) {
            Schema::table($key, function (Blueprint $table) {
                $table->dropForeign(['company_representative_id']);
            });
        }
    }
};
