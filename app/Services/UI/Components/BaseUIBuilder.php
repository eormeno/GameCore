<?php

namespace App\Services\UI\Components;

abstract class BaseUIBuilder
{
    protected string $id;
    protected array $config = [];
    protected string $type;

    public function __construct(string $id)
    {
        $this->type = $this->getTypeFromClassName();
        $this->id = $id . ':' . $this->type;
        $this->config = array_merge([
            'visible' => true,
        ], $this->getDefaultConfig());
    }

    private function getTypeFromClassName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();
        // Extrae la palabra antes de "Builder" y la convierte a minúsculas
        // Ej: "ButtonBuilder" -> "button", "LabelBuilder" -> "label"
        return strtolower(str_replace('Builder', '', $className));
    }

    abstract protected function getDefaultConfig(): array;

    public function visible(bool $visible = true): self
    {
        $this->config['visible'] = $visible;
        return $this;
    }

    public function build(): array
    {
        return [$this->id => $this->config];
    }
}