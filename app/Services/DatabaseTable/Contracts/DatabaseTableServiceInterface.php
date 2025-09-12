<?php

namespace App\Services\DatabaseTable\Contracts;

interface DatabaseTableServiceInterface
{
    /**
     * Get all database tables
     *
     * @param bool $showIgnored Whether to include ignored tables
     * @param bool $withData Show only tables containing data
     * @param string|null $pattern Optional pattern to filter tables
     * @return array Table information
     */
    public function getTables(bool $showIgnored = false, bool $withData = false, ?string $pattern = null): array;

    /**
     * Get table data
     *
     * @param string $tableName Name of the table
     * @param int $limit Maximum number of records to return
     * @param int $offset Number of records to skip
     * @return array Table data
     */
    public function getTableData(string $tableName, int $limit = 100, int $offset = 0): array;

    /**
     * Get the JSON representation of tables
     *
     * @param bool $showIgnored Whether to include ignored tables
     * @param bool $withData Show only tables containing data
     * @param string|null $pattern Optional pattern to filter tables
     * @param bool $pretty_print Whether to pretty print the JSON
     * @return string JSON representation of tables
     */
    public function getTablesJson(bool $showIgnored = false, bool $withData = false, ?string $pattern = null, bool $pretty_print = false): string;
}
