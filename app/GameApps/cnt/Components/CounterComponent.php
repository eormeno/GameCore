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
            'min' => ['integer', 0],
            'max' => ['integer', 100],
            'value' => ['integer', 0],
            'step' => ['integer', 1],
        ];
    }

    public function onAwake(array $initParams): void
    {
        $this->updateLabel();
    }

    public function onIncrementEvent(): void
    {
        $this->changeValue(1);
    }

    public function onDecrementEvent(): void
    {
        $this->changeValue(-1);
    }

    private function changeValue(int $sign): void
    {
        $newValue = $this->value + $sign * $this->step;
        if ($newValue < $this->min) {
            $newValue = $this->min;
        } elseif ($newValue > $this->max) {
            $newValue = $this->max;
        }
        if ($newValue === $this->value) {
            return;
        }
        $this->updateQuietly([
            'value' => $newValue
        ]);
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
