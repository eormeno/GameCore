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

    public function __construct(int $perPage = 10, int $currentPage = 1)
    {
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
    }

    /**
     * Get data for the current page
     * 
     * @return array
     */
    public function getPageData(): array
    {
        $offset = ($this->currentPage - 1) * $this->perPage;
        return $this->fetchData($offset, $this->perPage);
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
     * Get current page
     * 
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Set current page
     * 
     * @param int $page
     * @return self
     */
    public function setCurrentPage(int $page): self
    {
        $this->currentPage = max(1, $page);
        return $this;
    }

    /**
     * Get items per page
     * 
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Set items per page
     * 
     * @param int $perPage
     * @return self
     */
    public function setPerPage(int $perPage): self
    {
        $this->perPage = max(1, $perPage);
        return $this;
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
     * Check if there is a next page
     * 
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }

    /**
     * Check if there is a previous page
     * 
     * @return bool
     */
    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Get pagination info
     * 
     * @return array
     */
    public function getPaginationInfo(): array
    {
        return [
            'current_page' => $this->getCurrentPage(),
            'per_page' => $this->getPerPage(),
            'total_items' => $this->getTotalItems(),
            'total_pages' => $this->getTotalPages(),
            'has_next' => $this->hasNextPage(),
            'has_previous' => $this->hasPreviousPage(),
            'from' => (($this->currentPage - 1) * $this->perPage) + 1,
            'to' => min($this->currentPage * $this->perPage, $this->getTotalItems())
        ];
    }

    /**
     * Navigate to next page
     * 
     * @return self
     */
    public function nextPage(): self
    {
        if ($this->hasNextPage()) {
            $this->currentPage++;
        }
        return $this;
    }

    /**
     * Navigate to previous page
     * 
     * @return self
     */
    public function previousPage(): self
    {
        if ($this->hasPreviousPage()) {
            $this->currentPage--;
        }
        return $this;
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