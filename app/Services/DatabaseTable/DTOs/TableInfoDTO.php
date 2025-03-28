<?php

namespace App\Services\DatabaseTable\DTOs;

class TableInfoDTO
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var int
     */
    public int $rows;

    /**
     * @var int
     */
    public int $columns;

    /**
     * @param string $name
     * @param int $rows
     * @param int $columns
     */
    public function __construct(string $name, int $rows = 0, int $columns = 0)
    {
        $this->name = $name;
        $this->rows = $rows;
        $this->columns = $columns;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'rows' => $this->rows,
            'columns' => $this->columns
        ];
    }
}
