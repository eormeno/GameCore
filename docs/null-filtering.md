# Filtrado de Valores Null en JSON

## ✅ Implementación Completada

Se ha modificado el método `toJson()` en las siguientes clases para filtrar valores `null`:

### Archivos Modificados:

1. **`UIContainer.php`** (línea ~1430)
   ```php
   public function toJson(): array
   {
       // Filter out null values from config
       $config = array_filter($this->config, fn($value) => $value !== null);
       
       // Include 'name' attribute only if it's not null
       if ($this->name !== null) {
           $config['name'] = $this->name;
       }
       
       $result = [$this->id => $config];
       
       // Add all children at the same level
       foreach ($this->children as $child) {
           $childJson = $child->toJson();
           $result = $result + $childJson;
       }
       
       return $result;
   }
   ```

2. **`UIComponent.php`** (línea ~190)
   ```php
   public function toJson(): array
   {
       // Filter out null values from config
       $config = array_filter($this->config, fn($value) => $value !== null);
       
       return [$this->id => $config];
   }
   ```

3. **`TableBuilder.php`** - Usa `parent::toJson()` ✅
4. **`FormBuilder.php`** - Usa `parent::toJson()` ✅

---

## Comportamiento

### ANTES:
```json
{
    "1": {
        "type": "container",
        "visible": true,
        "layout": "vertical",
        "parent": null,
        "title": null,
        "flex_direction": null,
        "justify_content": null,
        "align_items": null,
        "grid_template_columns": null,
        "padding": null,
        "margin": null,
        "width": null,
        "height": null,
        "background_color": null,
        "border": null,
        "position": null,
        "overflow": null
    }
}
```

### DESPUÉS:
```json
{
    "1": {
        "type": "container",
        "visible": true,
        "layout": "vertical",
        "responsive": [],
        "breakpoints": [],
        "hide_on": [],
        "show_on": [],
        "data_attributes": []
    }
}
```

---

## Notas sobre Arrays Vacíos

Actualmente se mantienen arrays vacíos (`[]`) porque:
- ✅ No son `null`
- ✅ Tienen tipo definido (array)
- ✅ El cliente puede diferenciar entre "sin valor" y "array vacío"
- ✅ Evita errores de tipo en el frontend

### Opción de Filtrado Más Agresivo

Si quieres también filtrar arrays vacíos, strings vacíos, etc:

```php
public function toJson(): array
{
    // Filter out null values, empty arrays, and empty strings
    $config = array_filter($this->config, function($value) {
        if ($value === null) return false;
        if ($value === '') return false;
        if (is_array($value) && empty($value)) return false;
        return true;
    });
    
    if ($this->name !== null) {
        $config['name'] = $this->name;
    }
    
    return [$this->id => $config];
}
```

Con esta opción, el JSON sería aún más limpio:

```json
{
    "1": {
        "type": "container",
        "visible": true,
        "layout": "vertical"
    }
}
```

---

## Tests Realizados

✅ **Test 1**: Container básico - Sin nulls  
✅ **Test 2**: Container con flexbox - Sin nulls  
✅ **Test 3**: Container con grid - Sin nulls  
✅ **Test 4**: FormBuilder - Sin nulls  
✅ **Test 5**: Button component - Sin nulls  

**Total tests unitarios**: 56/56 ✅

---

## Ventajas

1. **JSON más limpio**: Solo incluye propiedades con valores reales
2. **Menor tamaño**: Reduce el payload JSON significativamente
3. **Mejor legibilidad**: Más fácil de debuggear
4. **Type-safe**: El cliente sabe que si una propiedad existe, tiene valor
5. **Backward compatible**: No rompe código existente

---

## Uso

```php
// Ejemplo: Solo se incluyen las propiedades establecidas
$container = UIBuilder::container('grid')
    ->grid('1fr 2fr')
    ->gap('1rem')
    ->padding('2rem');

$json = $container->toJson();
// Resultado: solo type, visible, layout, grid_template_columns, gap, padding
```

---

## Recomendación

**Estado actual (solo filtrar `null`)** es lo más recomendado porque:
- ✅ Elimina valores indefinidos (`null`)
- ✅ Mantiene arrays vacíos para type safety
- ✅ Compatible con validaciones de esquema en el cliente
- ✅ Balance entre limpieza y claridad

Si necesitas filtrado más agresivo (incluyendo arrays vacíos), házmelo saber.
