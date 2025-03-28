<?php

namespace App\Services\DatabaseTable\Drivers;

use Illuminate\Support\Facades\DB;

class MySqlDriver extends AbstractDatabaseDriver
{
    /**
     * @inheritdoc
     */
    public function getRawTables(): array
    {
        $connection = DB::connection();
        $tables = $connection->select('SHOW TABLES');

        return array_map(fn($table) => reset($table), $tables);
    }
}
