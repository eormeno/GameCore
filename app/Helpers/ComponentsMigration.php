<?php

namespace App\Helpers;

use Exception;
use App\Utils\ReflectionUtils;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Components\PersistentComponent;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;


class ComponentsMigration extends Migration
{
    public function __construct(
        private string $filename,
        private bool $logs = false
    ) {
    }

    public function up(): void
    {
        try {
            $tables = 0;
            ReflectionUtils::findClassesInPath($this->namespace($this->filename), function ($class) use (&$tables) {
                $table_name = $class->getMethod('getTable')->invoke(new $class->name);
                if ($this->logs)
                    print "Creating table $table_name\n";
                $config = $class->getMethod('config')->invoke(null);
                Schema::create($table_name, function (Blueprint $table) use ($config, &$tables) {
                    $table->id(); // PK and FK to components
                    $table->foreign('id')->references('id')->on('components')->onDelete('cascade');
                    foreach ($config as $column => $type) {
                        $macro = $type[0];
                        $default = $type[1];
                        $table->$macro($column)->default($default)->nullable();
                    }
                    $tables++;
                });
            }, PersistentComponent::class);

            if ($this->logs)
                print "Created {$tables} tables found in {$this->namespace($this->filename)}\n";
        } catch (DirectoryNotFoundException $e) {
            // print "Erro: {$e->getMessage()}\n"; // Uncomment this line if you want to log the error
        } catch (Exception $e) {
            print "Error: {$e->getMessage()}\n";
        }
    }

    public function down(): void
    {
        ReflectionUtils::findClassesInPath($this->namespace($this->filename), function ($class) {
            $table_name = $class->getMethod('getTable')->invoke(new $class->name);
            Schema::dropIfExists($table_name);
        }, PersistentComponent::class);
    }

    private function namespace(string $filename): string
    {
        $name = $filename;
        // extract the words between the underscores
        $words = explode('_', $name);
        // remove the last word (migrations)
        array_pop($words);
        $this->pascalize($words, ['common', 'components', 'services']);
        // join the words with a '/'
        $namespace = "GameApps/" . implode('/', $words);
        $path = app_path($namespace);
        if (!is_dir($path)) {
            throw new DirectoryNotFoundException("Folder $path does not exist");
        }
        return $namespace;
    }

    private function pascalize(array &$words, array $toPascalize = []): void
    {
        foreach ($words as &$word) {
            $lower = strtolower($word);
            if (in_array($lower, $toPascalize)) {
                $word = ucfirst($word);
            }
        }
    }


}
