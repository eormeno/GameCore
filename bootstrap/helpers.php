<?php

if (!function_exists('t')) {
    /**
     * Translate a slug using the CSV-based translation system
     * 
     * @param string $slug The translation slug (e.g., 'welcome_message' or 'games.new_game_created')
     * @param array $replace Array of parameters to replace in the translation (e.g., ['name' => 'Carlos'])
     * @return string The translated string
     */
    function t(string $slug, array $replace = []): string
    {
        $translationService = app(\App\Services\TranslationService::class);
        return $translationService->translate($slug, $replace);
    }
}

if (!function_exists('tb')) {
    /**
     * Translate multiple slugs in batch using the CSV-based translation system
     * 
     * @param array $slugs Array of slugs to translate
     * @return array Array of translated strings
     */
    function tb(array $slugs): array
    {
        $translationService = app(\App\Services\TranslationService::class);
        return $translationService->translateBatch($slugs);
    }
}

if (!function_exists('t_clear_cache')) {
    /**
     * Clear the translation cache
     * 
     * @return void
     */
    function t_clear_cache(): void
    {
        $translationService = app(\App\Services\TranslationService::class);
        $translationService->clearCache();
    }
}

if (!function_exists('t_missing')) {
    /**
     * Get missing translations report
     * 
     * @return array Array of missing translations by module and locale
     */
    function t_missing(): array
    {
        $translationService = app(\App\Services\TranslationService::class);
        return $translationService->getMissingTranslations();
    }
}