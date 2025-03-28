<?php

namespace App\Services\DatabaseTable\Drivers;

use Illuminate\Support\Facades\DB;

class PostgresDriver extends AbstractDatabaseDriver
{
    /**
     * @inheritdoc
     */
    public function getRawTables(): array
    {
        $connection = DB::connection();
        $tables = $connection->select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'");

        return array_map(fn($table) => $table->tablename, $tables);
    }
}
