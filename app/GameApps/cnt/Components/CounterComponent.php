<?php

namespace App\GameApps\cnt\Components;

use App\Traits\HasNamespacePrefix;
use App\Models\Components\PersistentComponent;

class CounterComponent extends PersistentComponent
{
    use HasNamespacePrefix;

    public static function config(): array
    {
        return [
            'value' => ['integer', 50],
        ];
    }

    public function onAwake(array $initParams): void
    {
        $this->updateLabel();
    }

    public function onIncrementEvent(): void
    {
        $this->incrementQuietly('value');
        $this->updateLabel();
    }

    public function onDecrementEvent(): void
    {
        $this->decrementQuietly('value');
        $this->updateLabel();
    }

    private function updateLabel(): void
    {
        $numberLabelGO = $this->findGameObject('number');
        $label = $numberLabelGO->getComponent('label');
        $label->updateQuietly([
            'text' => $this->value
        ]);
        $numberLabelGO->updateView();
    }
}
