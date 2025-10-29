<?php

namespace App\Services\UI\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent Data Table Model
 * 
 * Base implementation for Eloquent-based data sources.
 * Provides efficient database queries with pagination.
 */
abstract class EloquentDataTableModel extends AbstractDataTableModel
{
    protected string $modelClass;
    protected ?Builder $query = null;
    protected array $filters = [];

    public function __construct(string $modelClass, int $perPage = 10, int $currentPage = 1)
    {
        $this->modelClass = $modelClass;
        parent::__construct($perPage, $currentPage);
    }

    /**
     * Get the base query builder
     * Override this method to customize the query
     * 
     * @return Builder
     */
    protected function getBaseQuery(): Builder
    {
        if ($this->query === null) {
            $model = new $this->modelClass;
            $this->query = $model->newQuery();
            $this->applyQueryConstraints($this->query);
        }
        return clone $this->query;
    }

    /**
     * Apply query constraints (filters, joins, etc.)
     * Override this method in implementations
     * 
     * @param Builder $query
     * @return void
     */
    protected function applyQueryConstraints(Builder $query): void
    {
        // Override in implementations to add filters, joins, etc.
    }

    /**
     * Fetch data with offset and limit (optimized for database)
     * 
     * @param int $offset
     * @param int $limit
     * @return array
     */
    protected function fetchData(int $offset, int $limit): array
    {
        return $this->getBaseQuery()
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Count total items (optimized for database)
     * 
     * @return int
     */
    protected function countTotal(): int
    {
        return $this->getBaseQuery()->count();
    }

    /**
     * Get all data (not recommended for large datasets)
     * This method is kept for compatibility but should be avoided
     * 
     * @return array
     */
    protected function getAllData(): array
    {
        return $this->getBaseQuery()->get()->toArray();
    }

    /**
     * Find record by ID
     * 
     * @param int $id
     * @return Model|null
     */
    public function findById(int $id): ?Model
    {
        return $this->getBaseQuery()->find($id);
    }

    /**
     * Apply search filters
     * 
     * @param array $filters
     * @return self
     */
    public function applyFilters(array $filters): self
    {
        $this->query = null; // Reset query to apply new filters
        $this->filters = $filters;
        return $this;
    }

    /**
     * Apply sorting
     * 
     * @param string $column
     * @param string $direction
     * @return self
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $query = $this->getBaseQuery();
        $query->orderBy($column, $direction);
        $this->query = $query;
        return $this;
    }
}

/**
 * Example implementation for User model
 * This would be used when you have an actual User Eloquent model
 */
/*
class UserEloquentDataTableModel extends EloquentDataTableModel
{
    public function __construct(int $perPage = 10, int $currentPage = 1)
    {
        parent::__construct(\App\Models\User::class, $perPage, $currentPage);
    }

    protected function applyQueryConstraints(Builder $query): void
    {
        // Example: only active users
        $query->where('active', true);
        
        // Example: with relationships
        $query->with(['profile', 'roles']);
    }

    public function getColumns(): array
    {
        return [
            'id' => ['label' => 'ID', 'width' => [50, 80]],
            'name' => ['label' => 'Name', 'width' => [200, 250]],
            'email' => ['label' => 'Email', 'width' => [200, 300]],
            'created_at' => ['label' => 'Created', 'width' => [150, 200]],
            'actions' => ['label' => 'Actions', 'width' => [120, 150]]
        ];
    }

    public function getFormattedPageData(): array
    {
        $users = $this->getPageData();
        $formatted = [];

        foreach ($users as $index => $user) {
            $rowIndex = (($this->currentPage - 1) * $this->perPage) + $index;
            
            $formatted[] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'created_at' => date('Y-m-d', strtotime($user['created_at'])),
                'actions' => [
                    'button' => [
                        'label' => 'Edit',
                        'action' => 'edit_user',
                        'style' => 'primary',
                        'parameters' => [
                            'user_id' => $user['id'],
                            'row' => $rowIndex
                        ]
                    ]
                ]
            ];
        }

        return $formatted;
    }
}
*/