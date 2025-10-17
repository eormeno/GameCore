<?php

namespace App\Services\UI\Components;

use App\Services\UI\Support\UIIdGenerator;

abstract class BaseUIBuilder
{
    protected int $id;
    protected ?string $name;
    protected array $config = [];
    protected string $type;

    public function __construct(?string $name = null)
    {
        $this->name = $name;

        // Detectar automáticamente el contexto desde la clase que invoca
        $context = $this->detectCallingContext();
        
        // Usar el generador centralizado de IDs
        $this->id = UIIdGenerator::generate($context);
        
        $this->type = $this->getTypeFromClassName();
        $this->config = array_merge([
            'type' => $this->type,
            'name' => $this->name,
            'visible' => true,
        ], $this->getDefaultConfig());
    }

    /**
     * Detecta automáticamente la clase que está invocando el builder
     * Busca en el stack trace la primera clase fuera del namespace UI
     * 
     * @return string El nombre base de la clase invocante
     */
    private function detectCallingContext(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        
        // Buscar en el stack trace la primera clase que NO sea del namespace UI
        foreach ($trace as $frame) {
            if (isset($frame['class']) && 
                !str_starts_with($frame['class'], 'App\\Services\\UI\\')) {
                return class_basename($frame['class']);
            }
        }
        
        return 'default';
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

    public function name(?string $name): self
    {
        $this->config['name'] = $name;
        return $this;
    }

    public function build(): array
    {
        return [$this->id => $this->config];
    }
    
    /**
     * Método de utilidad para debugging - obtiene información del contexto
     * 
     * @param string $context Nombre del contexto
     * @return array Información del contexto (offset, contador, etc)
     */
    public static function getContextInfo(string $context): array
    {
        return UIIdGenerator::getContextInfo($context);
    }
}