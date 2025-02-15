<?php

namespace App\Utils;

use ReflectionClass;
use Illuminate\Support\Facades\File;

class ClassScanner
{
    public static function scanForProviders($filterInterface, string $namespace, string $directory): array
    {
        $providers = [];

        foreach (File::allFiles($directory) as $file) {
            $className = $namespace . '\\' . str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname()
            );

            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);

                // Verifica si implementa la interfaz MessageProviderInterface
                if ($reflection->implementsInterface($filterInterface) && !$reflection->isAbstract()) {
                    $providers[] = $className;
                }
            }
        }

        return $providers;
    }
}
