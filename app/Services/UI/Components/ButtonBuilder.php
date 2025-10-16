<?php

namespace App\Services\UI\Components;

class ButtonBuilder extends BaseUIBuilder
{
    protected function getDefaultConfig(): array
    {
        return [
            'enabled' => true,
            'style' => 'default',
            'label' => '',
            'action' => null,
            'parameters' => [],
            'icon' => null,
            'tooltip' => null,
        ];
    }

    public function label(string $label): self
    {
        $this->config['label'] = $label;
        return $this;
    }

    public function action(string $action, array $parameters = []): self
    {
        $this->config['action'] = $action;
        $this->config['parameters'] = $parameters;
        return $this;
    }

    public function icon(string $icon): self
    {
        $this->config['icon'] = $icon;
        return $this;
    }

    public function style(string $style): self
    {
        $this->config['style'] = $style;
        return $this;
    }

    public function enabled(bool $enabled = true): self
    {
        $this->config['enabled'] = $enabled;
        return $this;
    }

    public function tooltip(string $tooltip): self
    {
        $this->config['tooltip'] = $tooltip;
        return $this;
    }
}