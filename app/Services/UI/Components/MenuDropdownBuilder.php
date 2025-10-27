<?php

namespace App\Services\UI\Components;

use App\Services\UI\Support\UIIdGenerator;

/**
 * Menu Dropdown Builder
 * 
 * Builds dropdown menu structures with support for nested submenus
 */
class MenuDropdownBuilder
{
    private array $config = [];
    private array $items = [];
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->config = [
            'type' => 'menu_dropdown',
            'name' => $name,
            'items' => []
        ];
    }

    /**
     * Add a menu item
     * 
     * @param string $label Item label
     * @param string|null $action Action to trigger (optional if has submenu)
     * @param array $params Action parameters
     * @param string|null $icon Icon emoji or text
     * @param array $submenu Submenu items
     * @return self
     */
    public function item(
        string $label,
        ?string $action = null,
        array $params = [],
        ?string $icon = null,
        array $submenu = []
    ): self {
        $item = [
            'label' => $label,
            'action' => $action,
            'params' => $params,
            'icon' => $icon,
            'submenu' => $submenu
        ];

        $this->items[] = $item;
        return $this;
    }

    /**
     * Add a separator line
     * 
     * @return self
     */
    public function separator(): self
    {
        $this->items[] = [
            'type' => 'separator'
        ];
        return $this;
    }

    /**
     * Add a menu item with URL navigation
     * 
     * @param string $label Item label
     * @param string $url URL to navigate to
     * @param string|null $icon Icon emoji or text
     * @return self
     */
    public function link(string $label, string $url, ?string $icon = null): self
    {
        $item = [
            'label' => $label,
            'url' => $url,
            'icon' => $icon,
        ];

        $this->items[] = $item;
        return $this;
    }

    /**
     * Create a submenu structure
     * 
     * @param string $label Parent item label
     * @param string|null $icon Parent icon
     * @param callable $callback Callback to build submenu items
     * @return self
     */
    public function submenu(string $label, ?string $icon = null, callable $callback): self
    {
        $submenuBuilder = new self($label . '_submenu');
        $callback($submenuBuilder);
        
        $item = [
            'label' => $label,
            'icon' => $icon,
            'submenu' => $submenuBuilder->items
        ];

        $this->items[] = $item;
        return $this;
    }

    /**
     * Set the parent container for this menu
     * 
     * @param string $parentId Parent container ID or name
     * @return self
     */
    public function parent(string $parentId): self
    {
        $this->config['parent'] = $parentId;
        return $this;
    }

    /**
     * Build and return the menu configuration
     * 
     * @return array
     */
    public function build(): array
    {
        $this->config['items'] = $this->items;
        
        // Generate unique ID for this menu
        $id = UIIdGenerator::generate($this->name);
        $this->config['_id'] = $id;
        
        // Return as properly formatted UI component array
        return [
            $id => $this->config
        ];
    }
}
