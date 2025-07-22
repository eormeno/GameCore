<?php

/**
 * Este archivo ayuda a los IDEs a entender mejor las propiedades
 * dinámicas de Laravel y las relaciones entre modelos
 */

// IDE Helper for Laravel Models
// Instalar el paquete ide-helper para generar automáticamente esta documentación:
// composer require --dev barryvdh/laravel-ide-helper

// Ejecutar estos comandos para mantener la documentación actualizada:
// php artisan ide-helper:generate
// php artisan ide-helper:models
// php artisan ide-helper:meta

/**
 * Configuración recomendada para el archivo config/ide-helper.php
 * para mejorar el soporte del IDE
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Filename & Format
    |--------------------------------------------------------------------------
    |
    | The default filename (without extension) and the format (php or json)
    |
    */

    'filename'  => '_ide_helper',
    'format'    => 'php',
    'meta_filename' => '.phpstorm.meta.php',

    /*
    |--------------------------------------------------------------------------
    | Fluent helpers
    |--------------------------------------------------------------------------
    |
    | Set to true to generate commonly used Fluent methods
    |
    */

    'include_fluent' => true,

    /*
    |--------------------------------------------------------------------------
    | Write Model Magic methods
    |--------------------------------------------------------------------------
    |
    | Set to false to disable write magic methods of model
    |
    */

    'write_model_magic_where' => true,

    /*
    |--------------------------------------------------------------------------
    | Write Model External Eloquent methods
    |--------------------------------------------------------------------------
    |
    | Set to false to disable write external eloquent methods
    |
    */

    'write_model_external_builder_methods' => true,

    /*
    |--------------------------------------------------------------------------
    | Write Model relation count properties
    |--------------------------------------------------------------------------
    |
    | Set to false to disable writing of relation count properties to model DocBlocks.
    |
    */

    'write_model_relation_count_properties' => true,

    /*
    |--------------------------------------------------------------------------
    | Write Eloquent Model Mixins
    |--------------------------------------------------------------------------
    |
    | This will add the necessary DocBlock mixins to the model class
    | contained in the Laravel Framework. This helps the IDE with
    | auto-completion.
    |
    | Please be aware that this setting changes a file within the /vendor directory.
    |
    */

    'write_eloquent_model_mixins' => false,

    /*
    |--------------------------------------------------------------------------
    | Helper files to include
    |--------------------------------------------------------------------------
    |
    | Include helper files. By default not included, but can be toggled with the
    | -- helpers (-H) option. Extra helper files can be included.
    |
    */

    'include_helpers' => false,

    'helper_files' => [
        base_path().'/vendor/laravel/framework/src/Illuminate/Support/helpers.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Model locations to include
    |--------------------------------------------------------------------------
    |
    | Define in which directories the ide-helper:models command should look
    | for models.
    |
    */

    'model_locations' => [
        'app/Models',
        'app/Models/Components',
        'app/Models/GameObject',
        'app/Models/Events',
        'app/Models/Prefab',
        'app/GameApps/*/Services',
        'app/GameApps/*/Components',
    ],

    /*
    |--------------------------------------------------------------------------
    | Models to ignore
    |--------------------------------------------------------------------------
    |
    | Define which models should be ignored.
    |
    */

    'ignored_models' => [
        // Agregar aquí modelos que no necesiten documentación automática
    ],

    /*
    |--------------------------------------------------------------------------
    | Extra classes
    |--------------------------------------------------------------------------
    |
    | These implementations are not really extended, but called with magic methods
    |
    */

    'extra' => [
        'Eloquent' => ['Illuminate\Database\Eloquent\Builder', 'Illuminate\Database\Query\Builder'],
        'Session' => ['Illuminate\Session\Store'],
    ],

    'magic' => [],

    /*
    |--------------------------------------------------------------------------
    | Interface implementations
    |--------------------------------------------------------------------------
    |
    | These interfaces will be replaced with the implementing class. Some interfaces
    | are detected by the helpers, others can be listed below.
    |
    */

    'interfaces' => [
        // Agregar interfaces específicas del proyecto si es necesario
    ],

    /*
    |--------------------------------------------------------------------------
    | Support for custom DB types
    |--------------------------------------------------------------------------
    |
    | This setting allow you to map any custom database type (that you may have
    | created using CREATE TYPE statement or imported using database plugin
    | / extension to a Doctrine type.
    |
    | Each key in this array is a name of the Doctrine2 DBAL Platform. Currently valid names are:
    | 'postgresql', 'db2', 'drizzle', 'mysql', 'oracle', 'sqlanywhere', 'sqlite', 'mssql'
    |
    */

    'custom_db_types' => [
        // Agregar tipos de DB personalizados si es necesario
    ],

    /*
    |--------------------------------------------------------------------------
    | Support for camel cased models
    |--------------------------------------------------------------------------
    |
    | There are some Laravel packages (such as Eloquence) that allow for accessing
    | Eloquent model properties via camel case, instead of snake case.
    |
    | Enabling this option will tell the IDE helper to map snake_case columns to
    | their camelCase accessor/mutator methods.
    |
    */

    'model_camel_case_properties' => false,

    /*
    |--------------------------------------------------------------------------
    | Property Casts
    |--------------------------------------------------------------------------
    |
    | Cast the given "real type" to the given "type".
    |
    */
    'type_overrides' => [
        'integer' => 'int',
        'boolean' => 'bool',
        'double' => 'float',
        'real' => 'float',
    ],

    'include_class_docblocks' => false,
];
