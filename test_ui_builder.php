<?php

// Ejemplo de uso del nuevo sistema UI Builder

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\UI\UIBuilder;

// Simulamos el uso del sistema
echo "Ejemplo de uso del sistema UI Builder refactorizado:\n\n";

// Crear un botón
$button = UIBuilder::button('test_button')
    ->label('Haz clic aquí')
    ->action('test_action', ['param' => 'value'])
    ->icon('click')
    ->style('primary')
    ->enabled(true)
    ->tooltip('Este es un botón de prueba')
    ->build();

echo "Botón creado:\n";
echo json_encode($button, JSON_PRETTY_PRINT) . "\n\n";

// Crear un label
$label = UIBuilder::label('test_label')
    ->text('Este es un mensaje de prueba')
    ->style('warning')
    ->build();

echo "Label creado:\n";
echo json_encode($label, JSON_PRETTY_PRINT) . "\n\n";

// Crear una tabla
$table = UIBuilder::table('test_table')
    ->title('Mi tabla de prueba')
    ->headers([
        ['header' => 'Columna 1'],
        ['header' => 'Columna 2'],
        ['header' => 'Acciones']
    ])
    ->rows([
        ['Dato 1', 'Dato 2', 'Editar'],
        ['Dato 3', 'Dato 4', 'Eliminar']
    ])
    ->build();

echo "Tabla creada:\n";
echo json_encode($table, JSON_PRETTY_PRINT) . "\n\n";

// Crear un contenedor
$container = UIBuilder::container('test_container')
    ->slot('main')
    ->layout('vertical')
    ->title('Mi contenedor')
    ->elements(array_merge($button, $label, $table))
    ->build();

echo "Contenedor creado:\n";
echo json_encode($container, JSON_PRETTY_PRINT) . "\n";

echo "\n✅ Sistema UI Builder funcionando correctamente!\n";
echo "Nota: Los IDs automáticamente incluyen el sufijo del tipo de elemento.\n";