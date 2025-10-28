<?php

namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\UIBuilder;

class LandingDemoService extends AbstractUIService
{
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->shadow(false)
            ->padding(20);

        $container->add(
            UIBuilder::label('welcome')
                ->text('🚀 Bienvenido a GameCore UI Framework')
                ->style('h1')
                ->center()
        );

        return $container;
    }
}