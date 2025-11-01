<?php

namespace App\Services\UI\DataTable;

/**
 * Abstract Data Table Model
 * 
 * Provides pagination logic and data management for table components.
 * Implementations should override the data source methods.
 */
abstract class AbstractDataTableModel
{
    protected int $perPage;
    protected int $currentPage;
    protected ?int $totalItems = null;

    /**
     * Get table columns definition
     * 
     * This method should return an array defining the table columns,
     * including their names, types, and any other relevant metadata. For example:
     * 
     * [
     *     ['name' => 'id', 'type' => 'int'],
     *     ['name' => 'title', 'type' => 'string'],
     *     ['name' => 'created_at', 'type' => 'datetime'],
     * ]
     * 
     * @return array
     */
    abstract public function getColumns(): array;

    abstract public function getFormattedPageData(int $currentPage, int $perPage): array;

    /**
     * Get data for the current page
     * 
     * @return array
     */
    public function getPageData(int $currentPage, int $perPage): array
    {
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;

        $offset = ($currentPage - 1) * $perPage;
        return $this->fetchData($offset, $perPage);
    }

    /**
     * Get all data (for counting or other operations)
     * Override this method in implementations
     * 
     * @return array
     */
    abstract protected function getAllData(): array;

    /**
     * Fetch data with offset and limit
     * Default implementation uses getAllData() and array_slice
     * Override for more efficient database queries
     * 
     * @param int $offset
     * @param int $limit
     * @return array
     */
    protected function fetchData(int $offset, int $limit): array
    {
        $allData = $this->getAllData();
        return array_slice($allData, $offset, $limit);
    }

    /**
     * Get total number of items
     * 
     * @return int
     */
    public function getTotalItems(): int
    {
        if ($this->totalItems === null) {
            $this->totalItems = $this->countTotal();
        }
        return $this->totalItems;
    }

    /**
     * Count total items
     * Default implementation counts getAllData()
     * Override for more efficient counting
     * 
     * @return int
     */
    protected function countTotal(): int
    {
        return count($this->getAllData());
    }

    /**
     * Get total number of pages
     * 
     * @return int
     */
    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotalItems() / $this->perPage);
    }

    /**
     * Get the configuration for "removed" row display
     * 
     * Returns an array that defines how removed rows should appear.
     * Services can override this to customize the removal appearance.
     * 
     * @return array Configuration for removed row display
     */
    public function getRemovedRowConfig(): array
    {
        return [
            'primary_message' => '[REMOVED]',   // Main removal message
            'secondary_message' => '-',         // Secondary placeholder
            'id_placeholder' => '-',            // ID column placeholder
            'button_placeholder' => '-',        // Button column placeholder
            'empty_placeholder' => '',          // Empty cell placeholder
        ];
    }

    /**
     * Get removal values for all columns based on configuration
     * 
     * @param int $columnCount The number of columns
     * @return array Values for each column when row is removed
     */
    public function getRemovalValues(int $columnCount): array
    {
        $config = $this->getRemovedRowConfig();
        $values = [];

        for ($i = 0; $i < $columnCount; $i++) {
            if ($i === 0) {
                $values[$i] = $config['id_placeholder']; // ID column
            } elseif ($i === 1) {
                $values[$i] = $config['primary_message']; // Main content column
            } elseif ($i >= $columnCount - 2) {
                $values[$i] = $config['button_placeholder']; // Button columns (usually last 2)
            } else {
                $values[$i] = $config['secondary_message']; // Data columns
            }
        }

        return $values;
    }
}
