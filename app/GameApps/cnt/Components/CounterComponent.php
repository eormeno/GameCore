<?php

namespace App\GameApps\cnt\Components;

use App\Models\GameObject\GameObject;
use App\Models\Components\PersistentComponent;

class CounterComponent extends PersistentComponent
{

    public static function config(): array
    {
        return [
            'min' => ['integer', 0],
            'max' => ['integer', 100],
            'step' => ['integer', 10],
            'value' => ['integer', 0],
        ];
    }

    public function onAwake(array $initParams): void
    {
        $numberGO = $this->findGameObject('number');
        $label = $numberGO->getComponent('label');
        $label->update(['text' => $this->value]);
    }

    public function onIncrementEvent(): void
    {
        $numberGO = cache()->remember('numberGO', 60, function () {
            return $this->findGameObject('number');
        });
        $this->changeNumber($numberGO, 1);
    }

    public function onDecrementEvent(): void
    {
        $numberGO = cache()->remember('numberGO', 60, function () {
            return $this->findGameObject('number');
        });
        $this->changeNumber($numberGO, -1);
    }

    private function changeNumber(GameObject $numberGO, int $sign): void
    {
        $newValue = $this->value + $this->step * $sign;

        if ($newValue < $this->min) {
            $this->value = $this->min;
        } elseif ($newValue > $this->max) {
            $this->value = $this->max;
        } else {
            $this->value = $newValue;
        }

        if ($this->isDirty('value')) {
            $numberGO->increment('version');
            $label = cache()->remember('label', 30, function () use ($numberGO) {
                return $numberGO->getComponent('label');
            });
            $label->update(['text' => $this->value]);
            $this->save();
        }
    }
}
