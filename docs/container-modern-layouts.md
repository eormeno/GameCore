# Modern Container Layouts

Esta guía muestra cómo usar las nuevas características de layout moderno del UIContainer, incluyendo Flexbox, CSS Grid, diseño responsivo, y mucho más.

## Tabla de Contenidos

- [Flexbox Layouts](#flexbox-layouts)
- [CSS Grid Layouts](#css-grid-layouts)
- [Spacing y Sizing](#spacing-y-sizing)
- [Visual Styling](#visual-styling)
- [Diseño Responsivo](#diseño-responsivo)
- [Helpers Útiles](#helpers-útiles)
- [Ejemplos Completos](#ejemplos-completos)

---

## Flexbox Layouts

### Layout Horizontal (Row)

```php
$container = UIBuilder::container('header')
    ->flexRow()                    // Shortcut para flex + direction row
    ->justifyContent('space-between')
    ->alignItems('center')
    ->padding('1rem')
    ->gap('1rem');
```

### Layout Vertical (Column)

```php
$sidebar = UIBuilder::container('sidebar')
    ->flexColumn()                 // Shortcut para flex + direction column
    ->gap('0.5rem')
    ->padding('1rem')
    ->height('100vh');
```

### Centrar Contenido

```php
$modal = UIBuilder::container('modal')
    ->layout(LayoutType::FLEX)
    ->centerContent()              // justify-content + align-items center
    ->width('400px')
    ->height('300px');
```

### Flex Wrap y Responsive

```php
$cardGrid = UIBuilder::container('cards')
    ->layout(LayoutType::FLEX)
    ->flexWrap('wrap')
    ->gap('1rem')
    ->justifyContent('flex-start');

// Cada card puede tener flex-basis
$card = UIBuilder::container('card')
    ->flexBasis('300px')
    ->flexGrow(1)
    ->flexShrink(1);
```

### Control Avanzado de Flex Items

```php
$item = UIBuilder::container('flex-item')
    ->flexGrow(2)                  // Crecer 2x más que otros items
    ->flexShrink(0)                // No encogerse
    ->flexBasis('200px')           // Tamaño base
    ->order(3)                     // Orden en el layout
    ->alignSelf('flex-end');       // Alineación individual
```

---

## CSS Grid Layouts

### Grid Simple de Columnas

```php
$grid = UIBuilder::container('grid')
    ->gridColumns(3)               // Helper: 3 columnas iguales
    ->gap('1rem')
    ->padding('1rem');
```

### Grid Personalizado

```php
$layout = UIBuilder::container('layout')
    ->grid('200px 1fr 1fr', '100px auto')  // Columnas y filas
    ->gap('20px');
```

### Grid con Template Areas

```php
$page = UIBuilder::container('page')
    ->layout(LayoutType::GRID)
    ->gridTemplateColumns('200px 1fr')
    ->gridTemplateRows('60px 1fr 40px')
    ->gridTemplateAreas([
        'header header',
        'sidebar content',
        'footer footer'
    ])
    ->gap('0')
    ->minHeight('100vh');

// Los items se posicionan con gridArea()
$header = UIBuilder::container('header')->gridArea('header');
$sidebar = UIBuilder::container('sidebar')->gridArea('sidebar');
$content = UIBuilder::container('content')->gridArea('content');
$footer = UIBuilder::container('footer')->gridArea('footer');
```

### Grid Item Positioning

```php
// Item que ocupa 2 columnas y 3 filas
$featured = UIBuilder::container('featured')
    ->gridColumn('span 2')
    ->gridRow('span 3');

// Posicionamiento exacto
$specific = UIBuilder::container('item')
    ->gridColumn('2 / 4')          // Columna 2 a 4
    ->gridRow('1 / 3');            // Fila 1 a 3
```

### Grid Auto Flow

```php
$masonry = UIBuilder::container('masonry')
    ->layout(LayoutType::GRID)
    ->gridTemplateColumns('repeat(auto-fill, minmax(250px, 1fr))')
    ->gridAutoRows('100px')
    ->gridAutoFlow('dense')        // Rellena huecos
    ->gap('1rem');
```

---

## Spacing y Sizing

### Padding y Margin

```php
// Padding uniforme
$box = UIBuilder::container('box')
    ->padding('2rem');

// Padding individual
$custom = UIBuilder::container('custom')
    ->paddingEach('1rem', '2rem', '1rem', '2rem')  // top, right, bottom, left
    // O individual
    ->paddingTop('1rem')
    ->paddingRight('2rem');

// Margin similar
$spaced = UIBuilder::container('spaced')
    ->margin('1rem auto')          // Centrar horizontalmente
    ->marginTop('2rem');
```

### Tamaños

```php
$sized = UIBuilder::container('sized')
    ->width('600px')
    ->height('400px')
    ->minWidth('320px')
    ->maxWidth('1200px')
    ->minHeight('200px')
    ->maxHeight('90vh');

// Shortcuts
$full = UIBuilder::container('full')
    ->fullWidth()                  // width: 100%
    ->fullHeight();                // height: 100%
```

### Gap (Spacing entre hijos)

```php
// Gap uniforme
$grid = UIBuilder::container('grid')
    ->gap('1rem');

// Gap diferenciado
$custom = UIBuilder::container('custom')
    ->rowGap('2rem')
    ->columnGap('1rem');
```

---

## Visual Styling

### Colores y Backgrounds

```php
$styled = UIBuilder::container('styled')
    ->backgroundColor('#f0f0f0')
    ->backgroundImage('url(/images/bg.jpg)')
    ->backgroundSize('cover')
    ->backgroundPosition('center');
```

### Bordes y Sombras

```php
$card = UIBuilder::container('card')
    ->border('1px solid #ddd')
    ->rounded('12px')              // border-radius: 12px
    ->shadow('medium')             // Sombra predefinida
    ->padding('1.5rem');

// Sombras personalizadas
$custom = UIBuilder::container('custom')
    ->boxShadow('0 20px 40px rgba(0,0,0,0.3)');

// Intensidades de sombra
$light = UIBuilder::container()->shadow('light');    // Sutil
$medium = UIBuilder::container()->shadow('medium');  // Moderada
$heavy = UIBuilder::container()->shadow('heavy');    // Pronunciada
```

### Opacidad

```php
$overlay = UIBuilder::container('overlay')
    ->backgroundColor('black')
    ->opacity(0.7)
    ->position('absolute')
    ->fullWidth()
    ->fullHeight();
```

---

## Position y Overflow

### Posicionamiento

```php
// Fixed header
$header = UIBuilder::container('header')
    ->position('fixed')
    ->top('0')
    ->left('0')
    ->right('0')
    ->zIndex(100)
    ->backgroundColor('white')
    ->shadow('light');

// Absolute positioning
$badge = UIBuilder::container('badge')
    ->position('absolute')
    ->top('-10px')
    ->right('-10px')
    ->width('20px')
    ->height('20px')
    ->rounded('50%')
    ->backgroundColor('red');

// Sticky sidebar
$sidebar = UIBuilder::container('sidebar')
    ->position('sticky')
    ->top('20px');
```

### Overflow y Scroll

```php
// Container scrollable
$scrollable = UIBuilder::container('content')
    ->scrollable('y')              // Solo vertical
    ->scrollBehavior('smooth')
    ->maxHeight('500px');

// Scroll horizontal
$carousel = UIBuilder::container('carousel')
    ->scrollable('x')
    ->overflowY('hidden')
    ->flexRow()
    ->gap('1rem');

// Ocultar overflow
$clipped = UIBuilder::container('clipped')
    ->overflow('hidden')
    ->rounded('8px');
```

---

## Diseño Responsivo

### Responsive Configuration

```php
$container = UIBuilder::container('responsive')
    ->responsive([
        'mobile' => [
            'flex_direction' => 'column',
            'padding' => '1rem',
            'gap' => '0.5rem'
        ],
        'tablet' => [
            'flex_direction' => 'row',
            'padding' => '1.5rem',
            'gap' => '1rem'
        ],
        'desktop' => [
            'flex_direction' => 'row',
            'padding' => '2rem',
            'gap' => '2rem'
        ]
    ]);
```

### Hide/Show en Breakpoints

```php
// Ocultar en móvil
$desktopOnly = UIBuilder::container('desktop-only')
    ->hideOn(['mobile']);

// Mostrar solo en tablet y desktop
$noMobile = UIBuilder::container('no-mobile')
    ->showOn(['tablet', 'desktop']);

// Ocultar en múltiples breakpoints
$desktopExclusive = UIBuilder::container('desktop-exclusive')
    ->hideOn(['mobile', 'tablet']);
```

---

## Custom Styling

### Clases y Estilos Personalizados

```php
$custom = UIBuilder::container('custom')
    ->customClass('my-custom-container')
    ->customStyle('transform: translateY(-5px); transition: all 0.3s;')
    ->dataAttributes([
        'theme' => 'dark',
        'section' => 'hero',
        'analytics-id' => 'hero-section'
    ]);
```

---

## Helpers Útiles

### Shortcuts Comunes

```php
// Flex layouts
$row = UIBuilder::container()->flexRow();
$column = UIBuilder::container()->flexColumn();
$centered = UIBuilder::container()->layout(LayoutType::FLEX)->centerContent();

// Grid
$grid3 = UIBuilder::container()->gridColumns(3);
$grid4 = UIBuilder::container()->gridColumns(4);

// Sizing
$full = UIBuilder::container()->fullWidth()->fullHeight();

// Scrolling
$scroll = UIBuilder::container()->scrollable('y')->maxHeight('600px');

// Visual
$card = UIBuilder::container()
    ->rounded()                    // Redondeado predeterminado (8px)
    ->shadow()                     // Sombra predeterminada (medium)
    ->padding('1rem')
    ->backgroundColor('white');

// Spacing
$spaced = UIBuilder::container()->spacing('1rem');  // gap + padding

// Visibility
$hidden = UIBuilder::container()->hide();
$visible = UIBuilder::container()->show();
```

---

## Ejemplos Completos

### 1. Dashboard Layout

```php
$dashboard = UIBuilder::container('dashboard')
    ->layout(LayoutType::GRID)
    ->gridTemplateColumns('250px 1fr')
    ->gridTemplateRows('60px 1fr')
    ->gridTemplateAreas([
        'sidebar header',
        'sidebar content'
    ])
    ->gap('0')
    ->minHeight('100vh')
    ->backgroundColor('#f5f5f5');

$header = UIBuilder::container('header')
    ->gridArea('header')
    ->flexRow()
    ->justifyContent('space-between')
    ->alignItems('center')
    ->padding('0 2rem')
    ->backgroundColor('white')
    ->shadow('light');

$sidebar = UIBuilder::container('sidebar')
    ->gridArea('sidebar')
    ->flexColumn()
    ->gap('0.5rem')
    ->padding('1rem')
    ->backgroundColor('#2c3e50');

$content = UIBuilder::container('content')
    ->gridArea('content')
    ->padding('2rem')
    ->scrollable('y');
```

### 2. Card Grid Responsivo

```php
$cardGrid = UIBuilder::container('card-grid')
    ->layout(LayoutType::GRID)
    ->gridTemplateColumns('repeat(auto-fill, minmax(300px, 1fr))')
    ->gap('1.5rem')
    ->padding('2rem')
    ->responsive([
        'mobile' => [
            'grid_template_columns' => '1fr',
            'gap' => '1rem',
            'padding' => '1rem'
        ],
        'tablet' => [
            'grid_template_columns' => 'repeat(2, 1fr)',
            'gap' => '1.5rem'
        ]
    ]);

$card = UIBuilder::container('card')
    ->flexColumn()
    ->gap('1rem')
    ->padding('1.5rem')
    ->backgroundColor('white')
    ->rounded('12px')
    ->shadow('medium')
    ->border('1px solid #e0e0e0');
```

### 3. Modal Centered

```php
$modalOverlay = UIBuilder::container('modal-overlay')
    ->position('fixed')
    ->top('0')
    ->left('0')
    ->fullWidth()
    ->fullHeight()
    ->layout(LayoutType::FLEX)
    ->centerContent()
    ->backgroundColor('rgba(0,0,0,0.5)')
    ->zIndex(1000);

$modal = UIBuilder::container('modal')
    ->flexColumn()
    ->gap('1rem')
    ->width('500px')
    ->maxWidth('90vw')
    ->padding('2rem')
    ->backgroundColor('white')
    ->rounded('16px')
    ->shadow('heavy')
    ->responsive([
        'mobile' => [
            'width' => '100%',
            'height' => '100%',
            'border_radius' => '0',
            'max_width' => '100%'
        ]
    ]);
```

### 4. Flex Navigation

```php
$nav = UIBuilder::container('navigation')
    ->flexRow()
    ->justifyContent('space-between')
    ->alignItems('center')
    ->padding('1rem 2rem')
    ->backgroundColor('white')
    ->shadow('light')
    ->position('sticky')
    ->top('0')
    ->zIndex(100)
    ->responsive([
        'mobile' => [
            'flex_direction' => 'column',
            'gap' => '1rem',
            'padding' => '1rem'
        ]
    ]);

$navLeft = UIBuilder::container('nav-left')
    ->flexRow()
    ->gap('2rem')
    ->alignItems('center');

$navRight = UIBuilder::container('nav-right')
    ->flexRow()
    ->gap('1rem')
    ->alignItems('center');
```

### 5. Masonry Gallery

```php
$gallery = UIBuilder::container('gallery')
    ->layout(LayoutType::GRID)
    ->gridTemplateColumns('repeat(auto-fill, minmax(250px, 1fr))')
    ->gridAutoRows('10px')         // Filas pequeñas para efecto masonry
    ->gap('15px')
    ->padding('2rem');

// Cada imagen con altura variable
$imageCard1 = UIBuilder::container('image-1')
    ->gridRow('span 20')           // Ocupa 20 filas
    ->rounded('8px')
    ->overflow('hidden')
    ->shadow('medium');

$imageCard2 = UIBuilder::container('image-2')
    ->gridRow('span 30')           // Ocupa 30 filas
    ->rounded('8px')
    ->overflow('hidden')
    ->shadow('medium');
```

### 6. Sticky Sidebar Layout

```php
$layout = UIBuilder::container('layout')
    ->layout(LayoutType::GRID)
    ->gridTemplateColumns('300px 1fr')
    ->gap('2rem')
    ->padding('2rem')
    ->responsive([
        'mobile' => [
            'grid_template_columns' => '1fr',
            'gap' => '1rem',
            'padding' => '1rem'
        ]
    ]);

$sidebar = UIBuilder::container('sidebar')
    ->position('sticky')
    ->top('20px')
    ->maxHeight('calc(100vh - 40px)')
    ->scrollable('y')
    ->flexColumn()
    ->gap('1rem')
    ->hideOn(['mobile']);

$main = UIBuilder::container('main')
    ->flexColumn()
    ->gap('2rem');
```

### 7. Hero Section

```php
$hero = UIBuilder::container('hero')
    ->layout(LayoutType::FLEX)
    ->centerContent()
    ->flexColumn()
    ->height('100vh')
    ->backgroundImage('url(/images/hero.jpg)')
    ->backgroundSize('cover')
    ->backgroundPosition('center')
    ->position('relative');

$heroOverlay = UIBuilder::container('hero-overlay')
    ->position('absolute')
    ->fullWidth()
    ->fullHeight()
    ->backgroundColor('rgba(0,0,0,0.4)')
    ->zIndex(1);

$heroContent = UIBuilder::container('hero-content')
    ->flexColumn()
    ->gap('2rem')
    ->alignItems('center')
    ->padding('2rem')
    ->zIndex(2)
    ->maxWidth('800px')
    ->textAlign('center');
```

---

## Conclusión

Con estas nuevas características, UIContainer se convierte en una herramienta poderosa para crear layouts modernos y responsivos. Todas las propiedades CSS modernas están disponibles a través de una API fluida y fácil de usar.

**Ventajas:**

- ✅ **Flexbox completo**: Control total sobre layouts flexibles
- ✅ **CSS Grid**: Layouts complejos con template areas
- ✅ **Responsivo**: Configuración por breakpoint
- ✅ **Spacing moderno**: Gap, padding, margin
- ✅ **Visual styling**: Colores, sombras, bordes
- ✅ **Position control**: Absolute, fixed, sticky
- ✅ **Helpers útiles**: Shortcuts para patrones comunes
- ✅ **Type-safe**: Tipado fuerte en PHP
- ✅ **Method chaining**: API fluida y legible
- ✅ **Backward compatible**: No rompe código existente

**Próximos pasos recomendados:**

1. Crear tests para las nuevas características
2. Crear enums para valores comunes (FlexDirection, JustifyContent, etc.)
3. Documentar ejemplos visuales con screenshots
4. Integrar con FormBuilder para formularios modernos
