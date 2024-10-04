<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tableNames = [
        'senior_management_officer_documents',
        'senior_officer_identity_information',
        'senior_officer_residential_address',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableKeys = [
            'senior_management_officer',
            'senior_management_officer_documents',
            'senior_officer_identity_information',
            'senior_officer_residential_address'
        ];

        $sm = Schema::getConnection()->getDoctrineSchemaManager();

        foreach($tableKeys as $key) {
            $indexesFound = $sm->listTableIndexes($key);

            if(!array_key_exists('primary', $indexesFound)) {
                Schema::table($key, function (Blueprint $table) {
                    $table->uuid('id')->primary()->change();
                });
            }
        }

        foreach($this->tableNames as $key) {
            Schema::table($key, function (Blueprint $table) {
                $table->foreignUuid('senior_officer_id')
                    ->change()
                    ->references('id')
                    ->on('senior_management_officer')
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
        Schema::table('senior_management_officer', function (Blueprint $table) {
            $table->dropIndex(['id']);
        });

        Schema::table('senior_management_officer_documents', function (Blueprint $table) {
            $table->dropIndex(['id']);
        });

        Schema::table('senior_officer_identity_information', function (Blueprint $table) {
            $table->dropIndex(['id']);
        });

        Schema::table('senior_officer_residential_address', function (Blueprint $table) {
            $table->dropIndex(['id']);
        });

        foreach($this->tableNames as $key) {
            Schema::table($key, function (Blueprint $table) {
                $table->dropForeign(['senior_officer_id']);
            });
        }
    }
};
