<?php

namespace App\Console\Commands;

use App\Models\GameApp;
use Illuminate\Console\Command;

class GameAppsLoader
{
    protected Command $command;
    private array $result = ['created' => 0, 'updated' => 0];

    public function load(array $filesTree, Command $command): array
    {
        $this->command = $command;
        $this->updateGameApps($filesTree);
        return array_filter($this->result);
    }

    protected function updateGameApps(array $gameApps): void
    {
        foreach ($gameApps as $folder => $element) {
            if ($config = $element['config'] ?? null) {
                $config['prefix'] = $folder;
                $config['service_registry'] = $element['Services'] ?? [];
                $game_app = GameApp::updateOrCreate(
                    ['prefix' => $folder],
                    $config
                );
                if ($game_app->wasRecentlyCreated) {
                    $this->result['created']++;
                } else {
                    $this->result['updated']++;
                }
            }
        }
    }
}
