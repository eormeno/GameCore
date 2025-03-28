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
     * Get the JSON representation of tables
     *
     * @param bool $showIgnored Whether to include ignored tables
     * @param bool $withData Show only tables containing data
     * @param string|null $pattern Optional pattern to filter tables
     * @return string JSON representation of tables
     */
    public function getTablesJson(bool $showIgnored = false, bool $withData = false, ?string $pattern = null): string;
}
