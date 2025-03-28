<?php

namespace App\Services\DatabaseTable\Factories;

use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use App\Services\DatabaseTable\Drivers\MySqlDriver;
use App\Services\DatabaseTable\Drivers\SqliteDriver;
use App\Services\DatabaseTable\Drivers\PostgresDriver;
use App\Services\DatabaseTable\Drivers\AbstractDatabaseDriver;

class DatabaseTableServiceFactory
{
    /**
     * Create a database driver based on the current connection
     *
     * @return AbstractDatabaseDriver
     * @throws InvalidArgumentException
     */
    public static function createDriver(): AbstractDatabaseDriver
    {
        $driverName = DB::connection()->getDriverName();

        return match ($driverName) {
            'mysql' => new MySqlDriver(),
            'pgsql' => new PostgresDriver(),
            'sqlite' => new SqliteDriver(),
            default => throw new InvalidArgumentException("Unsupported database driver: {$driverName}")
        };
    }
}
