# GameCore Menu System Documentation

## Overview

The GameCore menu system provides a flexible and responsive navigation solution that supports multiple menu item types, submenus, icons, and keyboard accelerators. This documentation explains how to define and customize the menu structure.

## Menu Structure

The menu system consists of:

1. A container element (`.nav-container`)
2. The main menu (`.nav-menu`) 
3. Individual menu items (links and dropdown containers)
4. Submenus (`.submenu`)
5. Visual elements like icons and separators

## Defining Menu Items

### Basic Menu Item

A basic menu item is defined as a link with the `data-navigo` attribute (for routing):

```html
<a href="/path" data-navigo>Menu Text</a>
```

### Menu Item with Icon

Add an icon using Font Awesome:

```html
<a href="/path" data-navigo>
    <span class="menu-icon"><i class="fas fa-icon-name"></i></span>
    Menu Text
</a>
```

## Creating Submenus

Define a submenu using a container with the `has-submenu` class:

```html
<div class="menu-item has-submenu">
    <span class="menu-icon"><i class="fas fa-icon-name"></i></span>
    Menu Group Name
    <div class="submenu">
        <!-- Submenu items go here -->
        <a href="/sub-path" data-navigo>Submenu Item</a>
    </div>
</div>
```

## Special Menu Features

### Access Keys (Keyboard Accelerators)

Add keyboard shortcuts with the `access-key` class:

```html
<a href="/games" data-navigo>
    <span class="menu-icon"><i class="fas fa-gamepad"></i></span>
    <span class="access-key">G</span>ames
</a>
```

This creates Alt+G as a keyboard shortcut to that menu item. The underlined letter indicates the access key.

### Separators

Add visual separators between groups of submenu items:

```html
<div class="submenu">
    <a href="/path1" data-navigo>Item 1</a>
    <a href="/path2" data-navigo>Item 2</a>
    <div class="separator"></div>
    <a href="/path3" data-navigo>Item 3</a>
</div>
```

### Submenu Items with Icons

Add icons to submenu items:

```html
<div class="submenu">
    <a href="/path" data-navigo>
        <span class="menu-icon"><i class="fas fa-star"></i></span>
        Item with Icon
    </a>
</div>
```

## Complete Menu Example

```html
<div class="nav-container">
    <nav class="nav-menu">
        <!-- Simple menu item with icon -->
        <a href="/" data-navigo>
            <span class="menu-icon"><i class="fas fa-home"></i></span>Home
        </a>

        <!-- Menu item with access key -->
        <a href="/games" data-navigo>
            <span class="menu-icon"><i class="fas fa-gamepad"></i></span>
            <span class="access-key">G</span>ames
        </a>

        <!-- Dropdown menu with submenu -->
        <div class="menu-item has-submenu">
            <span class="menu-icon"><i class="fas fa-box-open"></i></span>Products
            <div class="submenu">
                <a href="/products?a=b#something-here" data-navigo>Products 1</a>
                <a href="/products?c=d" data-navigo>Products 2</a>
                <div class="separator"></div>
                <a href="/new-products" data-navigo>
                    <span class="menu-icon"><i class="fas fa-star"></i></span>New Releases
                </a>
            </div>
        </div>
    </nav>
</div>
```

## Mobile Support

The menu system automatically adapts to smaller screens:
- Submenus can be toggled with a click instead of hover
- The layout adjusts to vertical orientation

The mobile behavior is controlled by JavaScript event listeners that handle touch/click interactions to open submenus:

```javascript
document.querySelectorAll('.menu-item.has-submenu').forEach(item => {
    item.addEventListener('click', (e) => {
        // Toggle submenu on mobile
        if (window.innerWidth < 768) {
            const submenu = item.querySelector('.submenu');
            submenu.style.display = submenu.style.display === 'flex' ? 'none' : 'flex';
            e.preventDefault();
            e.stopPropagation();
        }
    });
});
```

## Active State Handling

The menu system tracks and highlights the current active menu item using the `updateActiveNav()` function which is called when routes change:

```javascript
const updateActiveNav = (url) => {
    // Reset all active states
    document.querySelectorAll('.nav-menu a, .nav-menu .menu-item').forEach(item => {
        item.classList.remove('active');
    });

    // Set active state for direct links
    document.querySelectorAll('.nav-menu a').forEach(link => {
        if (link.getAttribute('href') === url) {
            link.classList.add('active');

            // If link is in submenu, also highlight parent
            const parentMenuItem = link.closest('.submenu')?.parentElement;
            if (parentMenuItem) {
                parentMenuItem.classList.add('active');
            }
        }
    });
};
```

This system provides a versatile navigation menu that supports various UI patterns while maintaining a consistent visual style and responsive behavior across different screen sizes.
