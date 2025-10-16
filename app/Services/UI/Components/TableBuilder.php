<?php

namespace App\Services\UI\Components;

use App\Services\UI\Enums\TextAlign;
use App\Services\UI\Enums\FontWeight;

class TableBuilder extends BaseUIBuilder
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

    public function title(string $title): self
    {
        $this->config['title'] = $title;
        return $this;
    }

    public function headers(array $headers): self
    {
        $this->config['headers'] = $headers;
        return $this;
    }

    public function rows(array $rows): self
    {
        $this->config['rows'] = $rows;
        return $this;
    }

    public function pagination(bool $pagination = true): self
    {
        $this->config['pagination'] = $pagination;
        return $this;
    }
}