<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TranslationAddCommand extends Command
{
    protected $signature = 'translation:add 
                            {module : The module name (e.g., games, errors, common)}
                            {key : The translation key (e.g., new_feature, welcome_back)}
                            {english : The English translation text}
                            {--file=translations.csv : CSV file name}
                            {--slug= : Custom slug (defaults to module.key)}
                            {--copy-to-all : Copy English text to all other languages}
                            {--es= : Spanish translation (optional)}';
    
    protected $description = 'Add a new translation key to the CSV file';

    public function handle()
    {
        $module = $this->argument('module');
        $key = $this->argument('key');
        $english = $this->argument('english');
        $filename = $this->option('file');
        $filePath = resource_path("lang/$filename");
        
        // Generate slug
        $slug = $this->option('slug') ?: "$module.$key";
        
        // Check if file exists
        if (!file_exists($filePath)) {
            $this->error("❌ Translation file not found: $filePath");
            $this->info("💡 Run 'php artisan translation:init' to create the file first.");
            return 1;
        }
        
        $this->info("📋 Reading existing translations...");
        
        // Read existing translations
        $file = fopen($filePath, 'r');
        if (!$file) {
            $this->error("❌ Could not read file: $filePath");
            return 1;
        }
        
        $headers = fgetcsv($file);
        $translations = [];
        $slugExists = false;
        
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) >= 5) {
                $translations[] = $row;
                
                // Check if slug already exists
                if ($row[2] === $slug) {
                    $slugExists = true;
                }
            }
        }
        fclose($file);
        
        // Check for duplicate slug
        if ($slugExists) {
            $this->error("❌ Translation slug already exists: $slug");
            $this->info("💡 Use a different key or specify a custom slug with --slug option");
            return 1;
        }
        
        // Determine other language translations
        $spanish = $this->option('es');
        
        if (!$spanish) {
            if ($this->option('copy-to-all')) {
                $spanish = $english;
                $this->info("📝 Copying English text to Spanish (--copy-to-all option)");
            } else {
                $spanish = $this->ask("Spanish translation (leave empty to copy English text)", $english);
            }
        }
        
        // Create new translation row
        $newTranslation = [
            $module,    // Module
            $key,       // Key  
            $slug,      // Slug
            $english,   // EN
            $spanish    // ES
        ];
        
        // Add to translations array
        $translations[] = $newTranslation;
        
        $this->info("➕ Adding new translation...");
        $this->table(['Field', 'Value'], [
            ['Module', $module],
            ['Key', $key],
            ['Slug', $slug],
            ['English', $english],
            ['Spanish', $spanish]
        ]);
        
        // Write back to file
        $file = fopen($filePath, 'w');
        if (!$file) {
            $this->error("❌ Could not write to file: $filePath");
            return 1;
        }
        
        // Write headers
        fputcsv($file, $headers);
        
        // Write all translations
        foreach ($translations as $translation) {
            fputcsv($file, $translation);
        }
        
        fclose($file);
        
        $this->info("✅ Translation added successfully!");
        $this->info("📊 Total translations: " . count($translations));
        $this->info("📁 File: $filePath");
        
        // Show usage examples
        $this->info("");
        $this->info("💡 Usage examples:");
        $this->line("   t('$slug')");
        if (strpos($english, ':') !== false) {
            $this->line("   t('$slug', ['param' => 'value'])");
        }
        
        return 0;
    }
}