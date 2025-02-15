<?php

namespace App\Console\Commands;


use Illuminate\Support\Str;
use App\Utils\CaseConverters;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ReloadGameAppsCommand extends Command
{
    protected const APPS_PATH = 'GameApps';
    protected $signature = 'games';
    protected $description = 'Load all games applications configurations';

    public function handle(GameAppsLoader $gamesLoader, PrefabsLoader $prefabsLoader)
    {
        $this->info('Loading game apps elements...');
        $filesTree = $this->buildFilesTree(app_path(self::APPS_PATH));
        $game_apps = $gamesLoader->load($filesTree, $this);
        $prefabs = $prefabsLoader->load($filesTree, $this);
        $this->info(json_encode([
            'apps' => $game_apps,
            'prefabs' => $prefabs
        ], JSON_PRETTY_PRINT));
    }

    private function buildFilesTree($directory)
    {
        $result = [];
        if (!is_dir($directory)) {
            return $result;
        }
        $items = File::files($directory);
        foreach ($items as $file) {
            $extension = $file->getExtension();
            $fileName = $file->getFilename();
            if ($extension === 'php') {
                $fileName = $this->nameToSlug($file->getFilename());
                if ($this->isPhpFileAClass($file->getPathname())) {
                    $class_name = Str::before($file->getPathname(), '.php');
                    $class_name = 'App' . Str::after($class_name, app_path());
                    $class_name = str_replace('/', '\\', $class_name);
                    $result[$fileName] = $class_name;
                    continue;
                }
                $fileContent = include $file->getPathname();
                $result[$fileName] = $fileContent;
            } elseif ($extension === 'jpeg' || $extension === 'jpg') {
                $result[$fileName] = $file->getPath();
            }
        }
        $subdirectories = File::directories($directory);
        foreach ($subdirectories as $subdirectory) {
            $name = basename($subdirectory);
            $result[$name] = $this->buildFilesTree($subdirectory);
        }
        return $result;
    }

    private function isPhpFileAClass($file): bool
    {
        $content = file_get_contents($file);
        $tokens = token_get_all($content);
        $class_token = false;
        foreach ($tokens as $token) {
            if ($token[0] === T_CLASS) {
                $class_token = true;
            }
            if ($class_token && $token[0] === T_STRING) {
                return true;
            }
        }
        return false;
    }

    protected function nameToSlug(string $name): string
    {
        return CaseConverters::camelToKebab(preg_replace('/\.[^.]*$/', '', $name));
    }
}
