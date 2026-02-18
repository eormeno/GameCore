<?php

namespace App\GameApps\Common\Components;
use App\Models\Components\PersistentComponent;


class TextInputComponent extends PersistentComponent
{
    public static function config(): array
    {
        return [
            'event' => ['string', ''],
            'placeholder' => ['string', ''],
            'value' => ['string', ''],
            'style' => ['string', ''],
        ];
    }

    public function onAwake(array $initParams): void
    {
        $this->update($initParams);
    }

    public function view()
    {
        return [
            'parent' => $this->parentGameObject()->id ?? null,
            'type' => 'text_input',
            'event' => $this->event,
            'placeholder' => $this->placeholder,
            'value' => $this->value,
            'style' => $this->style,
        ];
    }
}