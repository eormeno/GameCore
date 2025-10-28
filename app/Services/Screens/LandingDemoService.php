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
        $container = UIBuilder::container('main');
        $container->parent('main');
        $container->layout(LayoutType::VERTICAL);
        $container->padding(20);

        $container->add(
            UIBuilder::label('welcome')
                ->text('Bienvenido a GameCore UI Framework')
                ->style('h1')
        );

        return $container;
    }
}