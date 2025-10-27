<?php

namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\MenuDropdownBuilder;
use App\Services\UI\Components\UIContainer;

/**
 * Demo Menu Service
 * 
 * Builds the main navigation menu for demo screens
 */
class DemoMenuService extends AbstractUIService
{
    protected function buildBaseUI(...$params): UIContainer
    {
        // For menu, we return a simple container that will hold the menu dropdown
        // The actual menu is built separately and returned as raw config
        $container = new UIContainer('menu_container');
        return $container;
    }

    public function getUI(...$params): array
    {
        $menu = new MenuDropdownBuilder('main_menu');
        
        // Set parent to render in #menu div
        $menu->parent('menu');

        // Demos submenu
        $menu->submenu('Demos', '🎮', function($submenu) {
            $submenu->link('Demo UI', '/demo/demo-ui', '🎨');
            $submenu->link('Table Demo', '/demo/table-demo', '📊');
            $submenu->link('Modal Demo', '/demo/modal-demo', '🪟');
            $submenu->link('Form Demo', '/demo/form-demo', '📝');
            $submenu->link('Button Demo', '/demo/button-demo', '🔘');
            $submenu->link('Input Demo', '/demo/input-demo', '⌨️');
            $submenu->link('Select Demo', '/demo/select-demo', '📋');
            $submenu->link('Checkbox Demo', '/demo/checkbox-demo', '☑️');
        });

        $menu->separator();

        // UI Components submenu (future components)
        $menu->submenu('Components', '🧩', function($submenu) {
            $submenu->link('Cards', '/demo/cards', '🃏');
            $submenu->link('Alerts', '/demo/alerts', '⚠️');
            $submenu->link('Tabs', '/demo/tabs', '�');
        });

        $menu->separator();

        // Settings
        $menu->link('Settings', '/demo/settings', '⚙️');
        
        // About
        $menu->link('About', '/demo/about', 'ℹ️');

        return $menu->build();
    }
}
