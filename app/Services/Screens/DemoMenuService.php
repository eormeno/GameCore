<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\AbstractUIService;
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
        // Menu doesn't use a container, but AbstractUIService requires this method.
        // Returning empty container - actual menu is built in getUI()
        return UIBuilder::container('_menu_placeholder');
        // Explicación: Este método buildBaseUI es necesario para cumplir con la interfaz
        // de AbstractUIService, pero en este caso no se utiliza para construir el menú real.
        // En su lugar, devolvemos un contenedor vacío llamado '_menu_placeholder' como marcador de posición.
        // El menú real se construye en el método getUI().
    }

    public function getUI(...$params): array
    {
        // Build menu using UIBuilder
        $menu = UIBuilder::menuDropdown('main_menu')
            ->parent('menu'); // Render in #menu div

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
            $submenu->link('Tabs', '/demo/tabs', '📑');
        });

        $menu->separator();

        // Settings
        $menu->link('Settings', '/demo/settings', '⚙️');
        
        // About
        $menu->link('About', '/demo/about', 'ℹ️');

        return $menu->build();
    }
}
