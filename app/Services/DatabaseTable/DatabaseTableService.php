<?php

namespace App\Services\DatabaseTable;

use Illuminate\Support\Collection;
use App\Services\DatabaseTable\Drivers\AbstractDatabaseDriver;
use App\Services\DatabaseTable\Factories\DatabaseTableServiceFactory;
use App\Services\DatabaseTable\Contracts\DatabaseTableServiceInterface;

class DatabaseTableService implements DatabaseTableServiceInterface
{
    /**
     * @var AbstractDatabaseDriver
     */
    protected AbstractDatabaseDriver $driver;

    /**
     * @param AbstractDatabaseDriver|null $driver
     */
    public function __construct(?AbstractDatabaseDriver $driver = null)
    {
        $this->driver = $driver ?? DatabaseTableServiceFactory::createDriver();
    }

    /**
     * @inheritdoc
     */
    public function getTables(bool $showIgnored = false, bool $withData = false, ?string $pattern = null): array
    {
        $tables = $this->driver->getTables($showIgnored, $withData, $pattern);

        return (new Collection($tables))
            ->map(fn($item) => $item->toArray())
            ->all();
    }

    /**
     * @inheritdoc
     */
    public function getTableData(string $tableName, int $limit = 100, int $offset = 0): array
    {
        return $this->driver->getTableData($tableName, $limit, $offset);
    }

    /**
     * @inheritdoc
     */
    public function getTablesJson(bool $showIgnored = false, bool $withData = false, ?string $pattern = null, bool $pretty_print = false): string
    {
        $tables = $this->getTables($showIgnored, $withData, $pattern);

        return json_encode([
            'tables' => $tables,
            'count' => count($tables)
        ], $pretty_print ? JSON_PRETTY_PRINT : 0);
    }
}
