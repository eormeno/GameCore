<?php

namespace App\Services\UI\Components;

use App\Services\UI\Contracts\UIElement;

/**
 * Abstract base class for all leaf UI components (Button, Label, Table, etc.)
 * 
 * This class implements the common functionality for leaf nodes in the UI tree.
 * Leaf components cannot have children and represent atomic UI elements.
 */
abstract class UIComponent implements UIElement
{
    private static array $autoIncPerContext = [];
    
    private int $_id;
    protected string $id;
    protected string $type;
    protected array $config = [];

    public function __construct(string $id)
    {
        // Detectar automáticamente el contexto desde la clase que invoca
        $context = $this->detectCallingContext();
        
        if (!isset(self::$autoIncPerContext[$context])) {
            self::$autoIncPerContext[$context] = 0;
        }
        
        $localId = ++self::$autoIncPerContext[$context];
        $offset = self::getContextOffset($context);
        
        $this->_id = $offset + $localId;
        $this->type = $this->getTypeFromClassName();
        $this->id = $id;
        $this->config = array_merge([
            'type' => $this->type,
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

    /**
     * Convierte el nombre de clase en un número único usando hash CRC32
     * Genera offsets en múltiplos de 10000 para evitar colisiones
     * 
     * @param string $context Nombre del contexto (clase invocante)
     * @return int Offset único para el contexto
     */
    private static function getContextOffset(string $context): int
    {
        if ($context === 'default') {
            return 0;
        }
        
        // Generar un hash numérico único del nombre de la clase usando CRC32
        $hash = crc32($context);
        
        // Convertir a positivo si es negativo y escalar al rango deseado
        // Múltiplos de 10000, máximo 9999 contextos diferentes
        $offset = (abs($hash) % 9999) * 10000;
        
        return $offset;
    }

    /**
     * Get the internal auto-incremental ID
     * 
     * @return int El ID interno único
     */
    public function getInternalId(): int
    {
        return $this->_id;
    }

    /**
     * Extract the component type from the class name
     * Example: "ButtonBuilder" -> "button"
     */
    private function getTypeFromClassName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();
        return strtolower(str_replace('Builder', '', $className));
    }

    /**
     * Get the default configuration for this component type
     * Must be implemented by each concrete component
     * 
     * @return array Default configuration values
     */
    abstract protected function getDefaultConfig(): array;

    /**
     * {@inheritDoc}
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function isVisible(): bool
    {
        return $this->config['visible'] ?? true;
    }

    /**
     * {@inheritDoc}
     */
    public function setVisible(bool $visible): self
    {
        $this->config['visible'] = $visible;
        return $this;
    }

    /**
     * Fluent API for setting visibility
     */
    public function visible(bool $visible = true): self
    {
        return $this->setVisible($visible);
    }

    /**
     * {@inheritDoc}
     * 
     * For leaf components, returns the configuration wrapped in the component ID
     */
    public function toJson(): array
    {
        return [$this->id => $this->config];
    }

    /**
     * Get the component configuration (without ID wrapper)
     * Useful for internal operations
     * 
     * @return array The configuration array
     */
    protected function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set a configuration value
     * 
     * @param string $key The configuration key
     * @param mixed $value The configuration value
     * @return self For method chaining
     */
    protected function setConfig(string $key, mixed $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * Método de utilidad para debugging - obtiene información del contexto
     * 
     * @param string $context Nombre del contexto
     * @return array Información del contexto (offset, contador, etc)
     */
    public static function getContextInfo(string $context): array
    {
        return [
            'context' => $context,
            'offset' => self::getContextOffset($context),
            'current_count' => self::$autoIncPerContext[$context] ?? 0,
        ];
    }
}
