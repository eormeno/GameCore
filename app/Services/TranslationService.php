<?php

namespace App\Services;

use Illuminate\Support\Facades\App;

class TranslationService
{
    private array $translationCache = [];
    private array $csvData = [];
    private string $currentLocale;
    private string $csvPath;
    private bool $csvLoaded = false;
    
    public function __construct()
    {
        $this->currentLocale = App::getLocale();
        $this->csvPath = storage_path('app/translations/' . config('translation.csv.default_filename', 'translations.csv'));
    }
    
    /**
     * Traduce un slug a texto según el idioma actual
     */
    public function translate(string $slug, array $replace = []): string
    {
        $locale = App::getLocale();
        
        if ($this->currentLocale !== $locale) {
            $this->clearCache();
            $this->currentLocale = $locale;
        }
        
        $cacheKey = "$locale.$slug";
        
        if (isset($this->translationCache[$cacheKey])) {
            $translation = $this->translationCache[$cacheKey];
            return empty($replace) ? $translation : $this->replaceParameters($translation, $replace);
        }
        
        $translation = $this->findTranslation($slug, $locale);
        $this->translationCache[$cacheKey] = $translation;
        
        return empty($replace) ? $translation : $this->replaceParameters($translation, $replace);
    }
    
    /**
     * Busca la traducción en el CSV
     */
    private function findTranslation(string $slug, string $locale): string
    {
        $this->loadCsvData();
        
        // 1. Buscar por slug exacto (ej: "games.new_game_created")
        if (isset($this->csvData[$slug][$locale])) {
            return $this->csvData[$slug][$locale];
        }
        
        // 2. Si no tiene módulo, buscar en módulos predefinidos
        if (!str_contains($slug, '.')) {
            $modules = $this->getSearchModules();
            
            foreach ($modules as $module) {
                $fullSlug = "$module.$slug";
                if (isset($this->csvData[$fullSlug][$locale])) {
                    return $this->csvData[$fullSlug][$locale];
                }
            }
        }
        
        // 3. Si no encuentra traducción, devuelve el slug formateado
        return $this->formatSlug($slug);
    }
    
    /**
     * Carga los datos del CSV en memoria
     */
    private function loadCsvData(): void
    {
        if ($this->csvLoaded) {
            return;
        }
        
        if (!file_exists($this->csvPath)) {
            $this->csvLoaded = true;
            return;
        }
        
        $file = fopen($this->csvPath, 'r');
        if (!$file) {
            $this->csvLoaded = true;
            return;
        }
        
        // Leer header
        $headers = fgetcsv($file);
        if (!$headers) {
            fclose($file);
            $this->csvLoaded = true;
            return;
        }
        
        // Identificar columnas de idiomas
        $localeColumns = [];
        $locales = config('app.available_locales', ['en', 'es']);
        
        for ($i = 3; $i < count($headers); $i++) {
            $headerLower = strtolower($headers[$i]);
            if (in_array($headerLower, $locales)) {
                $localeColumns[$headerLower] = $i;
            }
        }
        
        // Leer datos
        while (($row = fgetcsv($file)) !== false) {
            if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                continue;
            }
            
            $slug = $row[2]; // Columna "Slug"
            
            foreach ($localeColumns as $locale => $columnIndex) {
                $translation = isset($row[$columnIndex]) ? trim($row[$columnIndex]) : '';
                if (!empty($translation)) {
                    $this->csvData[$slug][$locale] = $translation;
                }
            }
        }
        
        fclose($file);
        $this->csvLoaded = true;
    }
    
    /**
     * Obtiene los módulos donde buscar traducciones
     */
    private function getSearchModules(): array
    {
        return config('translation.search_modules', [
            'games', 
            'common', 
            'errors', 
            'validation',
            'messages'
        ]);
    }
    
    /**
     * Formatea un slug cuando no hay traducción disponible
     */
    private function formatSlug(string $slug): string
    {
        // Remueve el módulo si existe
        $cleanSlug = str_contains($slug, '.') ? 
            substr($slug, strrpos($slug, '.') + 1) : $slug;
            
        return str_replace(['_', '-'], ' ', ucwords($cleanSlug, ' '));
    }
    
    /**
     * Reemplaza parámetros en la traducción
     */
    private function replaceParameters(string $translation, array $replace): string
    {
        foreach ($replace as $key => $value) {
            $translation = str_replace(":$key", $value, $translation);
        }
        return $translation;
    }
    
    /**
     * Obtiene todas las traducciones para un módulo específico
     */
    public function getModuleTranslations(string $module): array
    {
        $this->loadCsvData();
        $locale = App::getLocale();
        $moduleTranslations = [];
        
        foreach ($this->csvData as $slug => $translations) {
            if (str_starts_with($slug, "$module.") && isset($translations[$locale])) {
                $key = substr($slug, strlen($module) + 1);
                $moduleTranslations[$key] = $translations[$locale];
            }
        }
        
        return $moduleTranslations;
    }
    
    /**
     * Limpia el caché en memoria
     */
    public function clearCache(): void
    {
        $this->translationCache = [];
        $this->csvData = [];
        $this->csvLoaded = false;
    }
    
    /**
     * Pre-carga traducciones para módulos específicos
     */
    public function preloadModules(array $modules): void
    {
        $this->loadCsvData();
        // El CSV ya se carga completamente, no necesita pre-carga específica
    }
    
    /**
     * Traduce múltiples slugs de una vez (más eficiente para lotes)
     */
    public function translateBatch(array $slugs): array
    {
        $results = [];
        
        foreach ($slugs as $key => $slug) {
            $slugKey = is_numeric($key) ? $slug : $key;
            $results[$slugKey] = $this->translate(is_array($slug) ? $slug['key'] : $slug, 
                                                 is_array($slug) ? ($slug['replace'] ?? []) : []);
        }
        
        return $results;
    }
    
    /**
     * Obtiene estadísticas de traducciones faltantes
     */
    public function getMissingTranslations(): array
    {
        $this->loadCsvData();
        $locales = config('app.available_locales', ['en', 'es']);
        $modules = $this->getSearchModules();
        $missing = [];
        
        foreach ($modules as $module) {
            foreach ($this->csvData as $slug => $translations) {
                if (str_starts_with($slug, "$module.")) {
                    $key = substr($slug, strlen($module) + 1);
                    
                    foreach ($locales as $locale) {
                        if (!isset($translations[$locale]) || empty($translations[$locale])) {
                            $missing[$module][$locale][] = $key;
                        }
                    }
                }
            }
        }
        
        return $missing;
    }
    
    /**
     * Obtiene todas las traducciones del CSV
     */
    public function getAllTranslations(): array
    {
        $this->loadCsvData();
        return $this->csvData;
    }
}