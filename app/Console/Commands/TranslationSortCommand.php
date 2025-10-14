<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TranslationSortCommand extends Command
{
    protected $signature = 'translation:sort {--file=translations.csv}';
    protected $description = 'Sort the CSV translation file alphabetically by module and key';

    public function handle()
    {
        $filename = $this->option('file');
        $filePath = storage_path("app/translations/$filename");
        
        if (!file_exists($filePath)) {
            $this->error("❌ Translation file not found: $filePath");
            $this->info("💡 Run 'php artisan translation:init' to create the file first.");
            return 1;
        }
        
        $this->info("📋 Reading CSV file...");
        
        // Read CSV file
        $file = fopen($filePath, 'r');
        if (!$file) {
            $this->error("❌ Could not read file: $filePath");
            return 1;
        }
        
        $headers = fgetcsv($file);
        $translations = [];
        
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) >= 5) { // Ensure we have all required columns
                $translations[] = $row;
            }
        }
        fclose($file);
        
        $originalCount = count($translations);
        $this->info("📊 Found $originalCount translations to sort");
        
        // Sort by module first, then by key
        usort($translations, function ($a, $b) {
            // Compare module first
            $moduleComparison = strcasecmp($a[0], $b[0]);
            if ($moduleComparison !== 0) {
                return $moduleComparison;
            }
            
            // If modules are the same, compare keys
            return strcasecmp($a[1], $b[1]);
        });
        
        $this->info("🔄 Sorting translations...");
        
        // Write sorted data back to file
        $file = fopen($filePath, 'w');
        if (!$file) {
            $this->error("❌ Could not write to file: $filePath");
            return 1;
        }
        
        // Write headers
        fputcsv($file, $headers);
        
        // Write sorted translations
        foreach ($translations as $translation) {
            fputcsv($file, $translation);
        }
        
        fclose($file);
        
        $this->info("✅ CSV file sorted successfully!");
        $this->info("📊 Sorted $originalCount translations by module and key");
        $this->info("📁 File: $filePath");
        
        return 0;
    }
}