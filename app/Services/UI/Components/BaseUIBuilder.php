<?php

namespace App\Services\UI\Components;

abstract class BaseUIBuilder
{
    private static array $autoIncPerContext = [];
    
    private int $_id;
    protected string $id;
    protected array $config = [];
    protected string $type;

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