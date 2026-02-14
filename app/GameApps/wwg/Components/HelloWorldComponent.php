<?php

namespace App\GameApps\wwg\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class HelloWorldComponent extends PersistentComponent
{
    use HasNamespacePrefix;

    public static function config(): array
    {
        return [
            'text' => ['string', ''],
        ];
    }

    public function onAwake(array $initParams): void
    {
       // $this->updateLabel();
    }

    public function onStartingEvent(): void
    {
        $this->updateLabel();
    }

    private function updateLabel(): void
    {
        $titleGO = $this->findGameObject('title');
        $label = $titleGO->getComponent('label');
        $label->updateQuietly([
            'text' => $this->text
        ]);
        $titleGO->updateView();
    }
}
