<?php

namespace App\Services\UI\Support;

/**
 * Centralized ID generator for UI components
 * 
 * Ensures unique IDs across all UI elements (containers and components)
 * by maintaining a single auto-increment counter per context.
 */
class UIIdGenerator
{
    /** @var array<string, int> Auto-increment counter per context */
    private static array $autoIncPerContext = [];

    /**
     * Generate a unique ID for a UI element
     * 
     * @param string $context The calling context (class name)
     * @return int Unique ID
     */
    public static function generate(string $context): int
    {
        if (!isset(self::$autoIncPerContext[$context])) {
            self::$autoIncPerContext[$context] = 0;
        }

        $localId = ++self::$autoIncPerContext[$context];
        $offset = self::getContextOffset($context);

        return $offset + $localId;
    }

    /**
     * Get context information for debugging
     * 
     * @param string $context Context name
     * @return array Context information
     */
    public static function getContextInfo(string $context): array
    {
        return [
            'context' => $context,
            'offset' => self::getContextOffset($context),
            'current_count' => self::$autoIncPerContext[$context] ?? 0,
        ];
    }

    /**
     * Reset all counters (useful for testing)
     * 
     * @return void
     */
    public static function reset(): void
    {
        self::$autoIncPerContext = [];
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
}
