<?php

namespace App\Services\UI\Models;

/**
 * Abstract Data Table Model
 * 
 * Provides pagination and data management for table components.
 * Subclasses must implement data loading and column definitions.
 */
abstract class AbstractDataTableModel
{
    protected int $currentPage;
    protected int $itemsPerPage;
    protected array $cachedData = [];
    protected ?int $cachedTotalItems = null;

    public function __construct(int $itemsPerPage = 10, int $currentPage = 1)
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = max(1, $currentPage);
    }

    /**
     * Load all data from source (implemented by subclass)
     * 
     * @return array
     */
    abstract protected function loadAllData(): array;

    /**
     * Get column definitions for the table
     * Returns array of ['key' => 'label'] or ['key' => ['label' => 'Label', 'width' => [min, max]]]
     * 
     * @return array
     */
    abstract public function getColumns(): array;

    /**
     * Get table name/identifier
     * 
     * @return string
     */
    abstract public function getTableName(): string;

    /**
     * Get table title for display
     * 
     * @return string
     */
    abstract public function getTableTitle(): string;

    /**
     * Get all data (cached)
     * 
     * @return array
     */
    protected function getAllData(): array
    {
        if (empty($this->cachedData)) {
            $this->cachedData = $this->loadAllData();
        }
        return $this->cachedData;
    }

    /**
     * Get total number of items
     * 
     * @return int
     */
    public function getTotalItems(): int
    {
        if ($this->cachedTotalItems === null) {
            $this->cachedTotalItems = count($this->getAllData());
        }
        return $this->cachedTotalItems;
    }

    /**
     * Get total number of pages
     * 
     * @return int
     */
    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotalItems() / $this->itemsPerPage);
    }

    /**
     * Get current page number
     * 
     * @return int
     */
    public function getCurrentPage(): int
    {
        return min($this->currentPage, $this->getTotalPages() ?: 1);
    }

    /**
     * Set current page
     * 
     * @param int $page
     * @return static
     */
    public function setCurrentPage(int $page): static
    {
        $this->currentPage = max(1, min($page, $this->getTotalPages() ?: 1));
        return $this;
    }

    /**
     * Get items per page
     * 
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * Set items per page
     * 
     * @param int $itemsPerPage
     * @return static
     */
    public function setItemsPerPage(int $itemsPerPage): static
    {
        $this->itemsPerPage = max(1, $itemsPerPage);
        return $this;
    }

    /**
     * Get data for current page
     * 
     * @return array
     */
    public function getPageData(): array
    {
        $allData = $this->getAllData();
        $offset = ($this->getCurrentPage() - 1) * $this->itemsPerPage;
        return array_slice($allData, $offset, $this->itemsPerPage);
    }

    /**
     * Get data for specific page
     * 
     * @param int $page
     * @return array
     */
    public function getDataForPage(int $page): array
    {
        $originalPage = $this->currentPage;
        $this->setCurrentPage($page);
        $data = $this->getPageData();
        $this->currentPage = $originalPage; // Restore original page
        return $data;
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
            'total_pages' => $this->getTotalPages(),
            'total_items' => $this->getTotalItems(),
            'items_per_page' => $this->getItemsPerPage(),
            'has_previous' => $this->getCurrentPage() > 1,
            'has_next' => $this->getCurrentPage() < $this->getTotalPages(),
        ];
    }

    /**
     * Check if model has pagination enabled
     * 
     * @return bool
     */
    public function hasPagination(): bool
    {
        return $this->getTotalItems() > $this->itemsPerPage;
    }

    /**
     * Get formatted row data for table rendering
     * Override in subclass for custom formatting
     * 
     * @param array $item
     * @param int $rowIndex
     * @return array
     */
    public function formatRowData(array $item, int $rowIndex): array
    {
        $columns = $this->getColumns();
        $rowData = [];
        
        foreach ($columns as $key => $config) {
            $value = $item[$key] ?? '';
            $rowData[] = $this->formatCellValue($key, $value, $item, $rowIndex);
        }
        
        return $rowData;
    }

    /**
     * Format individual cell value
     * Override in subclass for custom formatting
     * 
     * @param string $columnKey
     * @param mixed $value
     * @param array $item
     * @param int $rowIndex
     * @return mixed
     */
    protected function formatCellValue(string $columnKey, mixed $value, array $item, int $rowIndex): mixed
    {
        return $value;
    }

    /**
     * Clear cached data (useful when data changes)
     * 
     * @return static
     */
    public function clearCache(): static
    {
        $this->cachedData = [];
        $this->cachedTotalItems = null;
        return $this;
    }
}