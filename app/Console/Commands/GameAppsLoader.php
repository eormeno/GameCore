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

    protected function updateGameApps(array $gameApps):void
    {
        foreach ($gameApps as $folder => $element) {
            if ($config = $element['config'] ?? null) {
                $config['prefix'] = $folder;
                $image_name = $config['image'];
                $image_path = $element['resources'][$image_name];
                unset($config['image']);
                $config['service_registry'] = $element['Services'] ?? [];
                $game_app = GameApp::where('prefix', $folder)->first();
                if ($game_app) {
                    $game_app->update($config);
                    $this->result['updated']++;
                } else {
                    GameApp::factory()->image($image_path, $image_name)->create($config);
                    $this->result['created']++;
                }
            }
        }
    }
}
