<?php

namespace Tests;

use DB;
use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // check if we should use the schema dump
        if (env('USE_SCHEMA_DUMP')) {
            // $this->loadSchemaDump();
        }

        return $app;
    }

    protected function loadSchemaDump()
    {
        // turn off foreign key checks
        DB::connection('mysql_testing')->statement('SET FOREIGN_KEY_CHECKS=0;');

        // get all table names
        $tables = DB::select('SHOW TABLES');

        // drop all tables
        foreach ($tables as $table) {
            $tableProps = (array) $table;
            $dbName = DB::connection('mysql_testing')->getDatabaseName();
            $tableName = $tableProps["Tables_in_$dbName"];
            DB::statement("DROP TABLE {$tableName}");
        }

        // turn foreign key checks back on
        DB::connection('mysql_testing')->statement('SET FOREIGN_KEY_CHECKS=1;');

        // load the schema dump
        DB::connection('mysql_testing')->unprepared(file_get_contents(database_path('schema/mysql-schema.sql')));
    }
}
