<?php

namespace App\Services\UI;

use App\Services\UI\Components\ButtonBuilder;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\ContainerBuilder;
use App\Services\UI\Components\TableBuilder;

class UIBuilder
{
    public static function button(string $id): ButtonBuilder
    {
        return new ButtonBuilder($id);
    }

    public static function label(string $id): LabelBuilder
    {
        return new LabelBuilder($id);
    }

    public static function container(string $id): ContainerBuilder
    {
        return new ContainerBuilder($id);
    }

    public static function table(string $id): TableBuilder
    {
        return new TableBuilder($id);
    }
}