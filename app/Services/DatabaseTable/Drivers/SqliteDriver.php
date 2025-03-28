<?php

namespace App\Services\DatabaseTable\Drivers;

use Illuminate\Support\Facades\DB;

class SqliteDriver extends AbstractDatabaseDriver
{
    /**
     * @inheritdoc
     */
    public function getRawTables(): array
    {
        $connection = DB::connection();
        $tables = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");

        return array_map(fn($table) => $table->name, $tables);
    }
}
