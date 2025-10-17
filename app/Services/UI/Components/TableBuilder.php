<?php

namespace App\Services\UI\Components;

use App\Services\UI\Enums\TextAlign;
use App\Services\UI\Enums\FontWeight;

/**
 * Builder for Table UI components
 * 
 * Tables are structured data display elements with headers and rows.
 * They support sorting, pagination, and custom styling.
 */
class TableBuilder extends UIComponent
{
    protected function getDefaultConfig(): array
    {
        return [
            'title' => '',
            'headers' => [],
            'rows' => [],
            'pagination' => false,
        ];
    }

    /**
     * Add a header to the table with optional configuration
     * 
     * @param string $text The header text
     * @param string|null $id Optional custom ID for the header
     * @param bool $sortable Whether the column is sortable
     * @param TextAlign $align Text alignment
     * @param string|null $width Column width (e.g., '200px')
     * @param FontWeight $fontWeight Font weight
     * @param string|null $color Text color
     * @param string|null $backgroundColor Background color
     * @param string|null $tooltip Tooltip text
     * @param string|null $sortDirection Initial sort direction
     * @return self For method chaining
     */
    public function addHeader(
        string $text,
        ?string $id = null,
        bool $sortable = false,
        TextAlign $align = TextAlign::CENTER,
        ?string $width = null,
        FontWeight $fontWeight = FontWeight::BOLD,
        ?string $color = null,
        ?string $backgroundColor = null,
        ?string $tooltip = null,
        ?string $sortDirection = null
    ): self {
        // Generate automatic ID if not provided
        $headerId = $id ?? strtolower(str_replace([' ', '(', ')', '-'], ['_', '', '', '_'], $text)) . '_header';
        
        $headerConfig = [
            $headerId . ':tableheader' => [
                'visible' => true,
                'text' => $text,
                'sortable' => $sortable,
                'sort_direction' => $sortDirection,
                'width' => $width,
                'align' => $align->value,
                'color' => $color,
                'background_color' => $backgroundColor,
                'font_weight' => $fontWeight->value,
                'tooltip' => $tooltip,
            ]
        ];

        $this->config['headers'] = array_merge($this->config['headers'], $headerConfig);
        return $this;
    }

    /**
     * Set the table title
     * 
     * @param string $title The table title
     * @return self For method chaining
     */
    public function title(string $title): self
    {
        return $this->setConfig('title', $title);
    }

    /**
     * Set all headers at once
     * 
     * @param array $headers Array of header configurations
     * @return self For method chaining
     */
    public function headers(array $headers): self
    {
        return $this->setConfig('headers', $headers);
    }

    /**
     * Set the table rows
     * 
     * @param array $rows Array of row data
     * @return self For method chaining
     */
    public function rows(array $rows): self
    {
        return $this->setConfig('rows', $rows);
    }

    /**
     * Enable or disable pagination
     * 
     * @param bool $pagination True to enable pagination
     * @return self For method chaining
     */
    public function pagination(bool $pagination = true): self
    {
        return $this->setConfig('pagination', $pagination);
    }

    /**
     * Legacy build method for backward compatibility
     * Returns array format instead of object
     * 
     * @return array
     * @deprecated Use toJson() instead
     */
    public function build(): array
    {
        return $this->toJson();
    }
}