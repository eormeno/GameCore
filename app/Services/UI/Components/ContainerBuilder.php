<?php

namespace App\Services\UI\Components;

use App\Services\UI\Enums\LayoutType;

class ContainerBuilder extends BaseUIBuilder
{
    protected function getDefaultConfig(): array
    {
        return [
            'layout' => LayoutType::VERTICAL->value,
            'elements' => [],
            'slot' => null,
            'title' => null,
        ];
    }

    public function slot(string $slot): self
    {
        $this->config['slot'] = $slot;
        return $this;
    }

    public function layout(LayoutType $layout): self
    {
        $this->config['layout'] = $layout->value;
        return $this;
    }

    public function title(string $title): self
    {
        $this->config['title'] = $title;
        return $this;
    }

    public function elements(array $elements): self
    {
        $this->config['elements'] = $elements;
        return $this;
    }
}