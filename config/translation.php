<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Translation Search Modules
    |--------------------------------------------------------------------------
    |
    | Define the modules where the translation service should search for
    | translations when a simple slug is provided (without module prefix).
    |
    */
    'search_modules' => [
        'games',
        'common', 
        'errors',
        'validation',
        'messages'
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Translation Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for translations to improve performance.
    |
    */
    'cache_enabled' => env('TRANSLATION_CACHE_ENABLED', true),
    'cache_ttl' => env('TRANSLATION_CACHE_TTL', 3600), // 1 hour
    
    /*
    |--------------------------------------------------------------------------
    | CSV Export/Import Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for CSV-based translation management.
    |
    */
    'csv' => [
        'default_filename' => 'translations.csv',
        'backup_on_import' => true,
        'auto_create_directories' => true,
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
    ],
];