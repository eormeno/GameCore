<?php

use Tests\Support\UITestHelper;

test("1. Menu service returns correct UI structure", function () {
    $response = $this->get("/api/demo-menu");
    $response->assertStatus(200);
    $ui = $response->json();
    write($response);

    // Assert UI structure using helper
    UITestHelper::assertUIStructure($ui);
    
    // Find the menu component
    $menuId = null;
    foreach ($ui as $id => $component) {
        if ($component['type'] === 'menu_dropdown') {
            $menuId = $id;
            break;
        }
    }
    
    expect($menuId)->not->toBeNull('Menu dropdown component should exist');
    
    // Verify menu component structure
    $menuComponent = $ui[$menuId];
    expect($menuComponent)
        ->toHaveKey('type')
        ->toHaveKey('name')
        ->toHaveKey('items')
        ->toHaveKey('parent')
        ->toHaveKey('_id');
    
    expect($menuComponent['type'])->toBe('menu_dropdown');
    expect($menuComponent['name'])->toBe('main_menu');
    expect($menuComponent['parent'])->toBe('menu');
    expect($menuComponent['items'])->toBeArray();
});

test("2. Menu contains all demo items", function () {
    $response = $this->get("/api/demo-menu");
    $response->assertStatus(200);
    $ui = $response->json();
    
    // Find the menu component
    $menuComponent = null;
    foreach ($ui as $component) {
        if ($component['type'] === 'menu_dropdown') {
            $menuComponent = $component;
            break;
        }
    }
    
    expect($menuComponent)->not->toBeNull();
    $items = $menuComponent['items'];
    expect($items)->not->toBeEmpty();
    
    // Count non-separator items
    $nonSeparatorItems = array_filter($items, fn($item) => !isset($item['type']) || $item['type'] !== 'separator');
    expect(count($nonSeparatorItems))->toBeGreaterThan(0);
    
    // Find "Demos" submenu
    $demosSubmenu = null;
    foreach ($items as $item) {
        if (isset($item['label']) && $item['label'] === 'Demos') {
            $demosSubmenu = $item;
            break;
        }
    }
    
    expect($demosSubmenu)->not->toBeNull('Demos submenu should exist');
    expect($demosSubmenu)->toHaveKey('submenu');
    expect($demosSubmenu['submenu'])->toBeArray();
    expect($demosSubmenu['icon'])->toBe('🎮');
});

test("3. Demos submenu contains expected items", function () {
    $response = $this->get("/api/demo-menu");
    $ui = $response->json();
    
    // Find menu and demos submenu
    $menuComponent = collect($ui)->first(fn($c) => $c['type'] === 'menu_dropdown');
    $demosSubmenu = collect($menuComponent['items'])
        ->first(fn($item) => isset($item['label']) && $item['label'] === 'Demos');
    
    expect($demosSubmenu['submenu'])->toBeArray();
    
    $submenuItems = $demosSubmenu['submenu'];
    $labels = array_column($submenuItems, 'label');
    
    // Verify expected demo items
    expect($labels)->toContain('Demo UI');
    expect($labels)->toContain('Table Demo');
    expect($labels)->toContain('Modal Demo');
    expect($labels)->toContain('Form Demo');
    expect($labels)->toContain('Button Demo');
    expect($labels)->toContain('Input Demo');
    expect($labels)->toContain('Select Demo');
    expect($labels)->toContain('Checkbox Demo');
});

test("4. Menu items have correct structure", function () {
    $response = $this->get("/api/demo-menu");
    $ui = $response->json();
    
    $menuComponent = collect($ui)->first(fn($c) => $c['type'] === 'menu_dropdown');
    $demosSubmenu = collect($menuComponent['items'])
        ->first(fn($item) => isset($item['label']) && $item['label'] === 'Demos');
    
    // Check first submenu item structure
    $firstItem = $demosSubmenu['submenu'][0];
    
    expect($firstItem)
        ->toHaveKey('label')
        ->toHaveKey('url')
        ->toHaveKey('icon');
    
    expect($firstItem['label'])->toBeString();
    expect($firstItem['url'])->toBeString();
    expect($firstItem['url'])->toStartWith('/demo/');
    expect($firstItem['icon'])->toBeString();
});

test("5. Menu contains separator elements", function () {
    $response = $this->get("/api/demo-menu");
    $ui = $response->json();
    
    $menuComponent = collect($ui)->first(fn($c) => $c['type'] === 'menu_dropdown');
    $items = $menuComponent['items'];
    
    // Count separators
    $separators = array_filter($items, fn($item) => isset($item['type']) && $item['type'] === 'separator');
    
    expect(count($separators))->toBeGreaterThan(0, 'Menu should have at least one separator');
});

test("6. Components submenu exists and has structure", function () {
    $response = $this->get("/api/demo-menu");
    $ui = $response->json();
    
    $menuComponent = collect($ui)->first(fn($c) => $c['type'] === 'menu_dropdown');
    
    // Find "Components" submenu
    $componentsSubmenu = collect($menuComponent['items'])
        ->first(fn($item) => isset($item['label']) && $item['label'] === 'Components');
    
    expect($componentsSubmenu)->not->toBeNull('Components submenu should exist');
    expect($componentsSubmenu)->toHaveKey('submenu');
    expect($componentsSubmenu['submenu'])->toBeArray();
    expect($componentsSubmenu['icon'])->toBe('🧩');
    
    // Verify has items
    expect(count($componentsSubmenu['submenu']))->toBeGreaterThan(0);
});

test("7. Menu has Settings and About items", function () {
    $response = $this->get("/api/demo-menu");
    $ui = $response->json();
    
    $menuComponent = collect($ui)->first(fn($c) => $c['type'] === 'menu_dropdown');
    $items = $menuComponent['items'];
    
    $labels = array_column($items, 'label');
    
    expect($labels)->toContain('Settings');
    expect($labels)->toContain('About');
    
    // Verify Settings has correct icon and url
    $settings = collect($items)->first(fn($item) => isset($item['label']) && $item['label'] === 'Settings');
    expect($settings['icon'])->toBe('⚙️');
    expect($settings['url'])->toBe('/demo/settings');
    
    // Verify About has correct icon and url
    $about = collect($items)->first(fn($item) => isset($item['label']) && $item['label'] === 'About');
    expect($about['icon'])->toBe('ℹ️');
    expect($about['url'])->toBe('/demo/about');
});

test("8. Menu component has unique ID", function () {
    $response = $this->get("/api/demo-menu");
    $ui = $response->json();
    
    // Get the menu component ID (key in array)
    $menuId = collect($ui)
        ->keys()
        ->first(fn($id) => $ui[$id]['type'] === 'menu_dropdown');
    
    expect($menuId)->toBeInt();
    expect($menuId)->toBeGreaterThan(0);
    
    // Verify _id matches the key
    expect($ui[$menuId]['_id'])->toBe($menuId);
});

test("9. All submenu items are link type with URL", function () {
    $response = $this->get("/api/demo-menu");
    $ui = $response->json();
    
    $menuComponent = collect($ui)->first(fn($c) => $c['type'] === 'menu_dropdown');
    $demosSubmenu = collect($menuComponent['items'])
        ->first(fn($item) => isset($item['label']) && $item['label'] === 'Demos');
    
    foreach ($demosSubmenu['submenu'] as $item) {
        expect($item)->toHaveKey('url');
        expect($item['url'])->toMatch('/^\/demo\/.+/');
        expect($item)->not->toHaveKey('action', 'Submenu items should use URLs not actions');
    }
});

test("10. Menu structure is valid for frontend rendering", function () {
    $response = $this->get("/api/demo-menu");
    $ui = $response->json();
    
    // Verify it's an array indexed by ID
    expect($ui)->toBeArray();
    expect(count($ui))->toBe(1, 'Should return exactly one component');
    
    $menuId = array_key_first($ui);
    $menuComponent = $ui[$menuId];
    
    // Verify required fields for frontend
    expect($menuComponent)->toHaveKeys([
        'type',
        'name', 
        'items',
        'parent',
        '_id'
    ]);
    
    // Verify parent is 'menu' (for rendering in #menu div)
    expect($menuComponent['parent'])->toBe('menu');
    
    // Verify items array is not empty
    expect($menuComponent['items'])->not->toBeEmpty();
});
