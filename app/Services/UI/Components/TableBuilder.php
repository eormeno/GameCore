<?php

namespace App\Services\UI\Components;

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