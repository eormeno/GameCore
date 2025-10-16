<?php

namespace App\Services\UI\Components;

class LabelBuilder extends BaseUIBuilder
{
    protected function getDefaultConfig(): array
    {
        return [
            'text' => '',
            'style' => 'default',
        ];
    }

    public function text(string $text): self
    {
        $this->config['text'] = $text;
        return $this;
    }

    public function style(string $style): self
    {
        $this->config['style'] = $style;
        return $this;
    }
}