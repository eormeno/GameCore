<?php

namespace App\GameApps\cnt\Components;

use App\Models\Components\PersistentComponent;

class CounterComponent extends PersistentComponent
{

    public static function config(): array
    {
        return [
            'value' => ['integer', 50],
        ];
    }

    public function onAwake(array $initParams): void
    {
        $numberLabelGO = $this->findGameObject('number');
        $label = $numberLabelGO->getComponent('label');
        $label->text = $this->value;
        $label->save();
        $numberLabelGO->updateView();
    }

    public function onIncrementEvent(): void
    {
        $this->increment('value');
        $this->updateLabel();
    }

    public function onDecrementEvent(): void
    {
        $this->decrement('value');
        $this->updateLabel();
    }

    private function updateLabel(): void
    {
        $numberLabelGO = $this->findGameObject('number');
        $label = $numberLabelGO->getComponent('label');
        $label->text = $this->value;
        $label->save();
        $numberLabelGO->updateView();
    }
}
