<?php

namespace App\Services\DatabaseTable\Drivers;

use App\Services\DatabaseTable\DTOs\TableInfoDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

abstract class AbstractDatabaseDriver
{
    /**
     * Get raw table names from the database
     *
     * @return array
     */
    abstract public function getRawTables(): array;

    /**
     * Get tables with additional information
     *
     * @param bool $showIgnored Whether to include ignored tables
     * @param bool $withData Show only tables containing data
     * @param string|null $pattern Optional pattern to filter tables
     * @return array<TableInfoDTO>
     */
    public function getTables(bool $showIgnored = false, bool $withData = false, ?string $pattern = null): array
    {
        $tables = $this->getRawTables();

        // Filter out ignored tables if not showing ignored
        if (!$showIgnored) {
            $tables = $this->filterIgnoredTables($tables);
        }

        // Apply wildcard filtering if a pattern is provided
        if ($pattern) {
            $tables = $this->applyPatternFilter($tables, $pattern);
        }

        // Create TableInfoDTO objects with detailed information
        $tableData = [];
        foreach ($tables as $tableName) {
            // Get column count
            $columns = Schema::getColumnListing($tableName);
            $columnCount = count($columns);

            // Get row count
            $rowCount = DB::table($tableName)->count();

            // Skip tables with no rows if --with-data option is used
            if ($withData && $rowCount == 0) {
                continue;
            }

            $tableData[] = new TableInfoDTO($tableName, $rowCount, $columnCount);
        }

        return $tableData;
    }

    /**
     * Get data from a specific table
     *
     * @param string $tableName Name of the table
     * @param int $limit Maximum number of records to return
     * @param int $offset Number of records to skip
     * @return array Table data
     */
    public function getTableData(string $tableName, int $limit = 100, int $offset = 0): array
    {
        // Validate table exists and is not in ignored list
        if (!Schema::hasTable($tableName)) {
            throw new \InvalidArgumentException("Table '{$tableName}' does not exist.");
        }

        // Check if table is in ignored list (for security)
        $ignoredTables = Config::get('tables.ignore', []);
        if (in_array($tableName, $ignoredTables)) {
            throw new \InvalidArgumentException("Access to table '{$tableName}' is not allowed.");
        }

        // Get table data with pagination
        return DB::table($tableName)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Filter out tables that should be ignored according to config
     *
     * @param array $tables List of table names to filter
     * @return array Filtered list of table names
     */
    protected function filterIgnoredTables(array $tables): array
    {
        // Get ignore config
        $ignoredTables = Config::get('tables.ignore', []);
        $ignoredPatterns = Config::get('tables.ignore_patterns', []);

        // Filter out exact table name matches
        $filteredTables = array_filter($tables, function ($tableName) use ($ignoredTables) {
            return !in_array($tableName, $ignoredTables);
        });

        // Filter out pattern matches
        return array_filter($filteredTables, function ($tableName) use ($ignoredPatterns) {
            foreach ($ignoredPatterns as $pattern) {
                // Convert SQL-like wildcards to shell-style wildcards if present
                $pattern = str_replace('%', '*', $pattern);
                $pattern = str_replace('_', '?', $pattern);

                if (fnmatch($pattern, $tableName)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Apply pattern filtering to tables
     *
     * @param array $tables List of table names to filter
     * @param string $pattern Pattern to filter by
     * @return array Filtered list of table names
     */
    protected function applyPatternFilter(array $tables, string $pattern): array
    {
        return array_filter($tables, function ($tableName) use ($pattern) {
            // Convert SQL-like wildcards (% and _) to shell-style wildcards (* and ?)
            $shellPattern = str_replace('%', '*', $pattern);
            $shellPattern = str_replace('_', '?', $shellPattern);

            return fnmatch($shellPattern, $tableName);
        });
    }
}
