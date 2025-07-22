<?php
/**
 * Script para generar documentación de propiedades en modelos de Laravel
 * para mejorar el soporte del IDE y evitar errores de "Undefined property"
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

function generateModelDocumentation($modelClass) {
    if (!class_exists($modelClass)) {
        echo "Clase no encontrada: $modelClass\n";
        return;
    }

    $reflection = new ReflectionClass($modelClass);
    $instance = new $modelClass();

    // Obtener información de la tabla
    $tableName = $instance->getTable();

    echo "Generando documentación para: $modelClass\n";
    echo "Tabla: $tableName\n";

    // Propiedades básicas que todos los modelos tienen
    $properties = [
        'int $id',
    ];

    // Agregar fillable properties
    $fillable = $instance->getFillable();
    foreach ($fillable as $field) {
        if ($field !== 'id') {
            $properties[] = "mixed \$$field";
        }
    }

    // Agregar timestamps si están habilitados
    if ($instance->timestamps) {
        $properties[] = '\Illuminate\Support\Carbon|null $created_at';
        $properties[] = '\Illuminate\Support\Carbon|null $updated_at';
    }

    // Generar el docblock
    $docblock = "/**\n";
    foreach ($properties as $property) {
        $docblock .= " * @property $property\n";
    }
    $docblock .= " */";

    echo $docblock . "\n\n";
}

// Lista de modelos para documentar
$models = [
    'App\Models\GameApp',
    'App\Models\Game',
    'App\Models\User',
    'App\Models\GameService',
    'App\Models\GameObject\Base',
    'App\Models\Components\ComponentBase',
    'App\Models\Events\GameAppEvent',
    'App\Models\Events\GameEventListenerManager',
    'App\Models\Prefab\Base',
];

echo "=== GENERADOR DE DOCUMENTACIÓN DE MODELOS ===\n\n";

foreach ($models as $model) {
    generateModelDocumentation($model);
}

echo "=== FINALIZADO ===\n";
