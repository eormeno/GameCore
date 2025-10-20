# Test Files Manual

Estos son archivos de test independientes (fuera de Pest) que demuestran funcionalidades específicas del sistema UI.

## 📝 Tests Disponibles

### test_new_header_system.php
**Propósito:** Demostrar el nuevo sistema de headers de tablas con `TableHeaderRowBuilder` y `TableHeaderCellBuilder`.

**Características probadas:**
- Creación de header row con `createHeaderRow()`
- Headers con sortable, actions, tooltip
- Headers con colspan
- Headers con estilos (width, align, backgroundColor)
- Verificación de estructura JSON

**Ejecutar:**
```bash
php test_new_header_system.php
```

**Output esperado:**
- JSON con estructura de tabla completa
- Verificación de IDs y referencias parent
- Detalles de cada header cell

---

### test_table_cell.php
**Propósito:** Probar el comportamiento de `TableCellBuilder`.

**Características probadas:**
- Cell con texto simple
- Cell con alineación (left/center/right)
- Cell con componente hijo (button)
- Múltiples cells en una row
- Restricción de un solo hijo por cell
- Referencias parent correctas

**Ejecutar:**
```bash
php test_table_cell.php
```

**Output esperado:**
- 5 tests con PASS/FAIL
- Verificación de estructura JSON
- Excepción cuando se intenta agregar segundo hijo

---

### test_simple_cell.php
**Propósito:** Test mínimo para verificar creación básica de cells.

**Características probadas:**
- Crear tabla, row y cell
- Asignar texto a cell
- Generar JSON sin errores

**Ejecutar:**
```bash
php test_simple_cell.php
```

**Output esperado:**
- Mensajes de progreso
- JSON con estructura mínima
- "Success!" al final

---

### test_null_filter.php
**Propósito:** Verificar que el sistema filtra valores null del JSON.

**Características probadas:**
- Container básico sin nulls
- Container con flexbox
- Container con grid y responsive
- FormBuilder sin nulls
- Button component sin nulls

**Ejecutar:**
```bash
php test_null_filter.php
```

**Output esperado:**
- 5 tests passed
- "All null values have been successfully filtered from JSON output!"

---

## 🚫 Tests Eliminados

Los siguientes tests fueron eliminados porque usan la API deprecated `addHeader()`:

- ❌ test_autofill_new.php
- ❌ test_autofill_rows.php
- ❌ test_minrows.php
- ❌ test_table_headers.php
- ❌ test_visible_header.php
- ❌ test_row_empty.php

**Razón:** El método `addHeader()` está deprecated. Se debe usar `createHeaderRow()->createCell()` en su lugar.

---

## 🧪 Tests de Pest (tests/)

Para tests unitarios completos, usa los tests de Pest:

```bash
# Todos los tests de UI
./vendor/bin/pest tests/Unit/Services/UI/

# Test específico de headers
./vendor/bin/pest tests/Unit/Services/UI/TableHeaderTest.php

# Todos los tests del proyecto
./vendor/bin/pest
```

---

## ✅ Best Practices

1. **Usa Pest para tests unitarios** - Estos archivos son solo para demostración
2. **No uses addHeader()** - Está deprecated, usa `createHeaderRow()`
3. **Verifica el JSON** - Usa `json_encode(..., JSON_PRETTY_PRINT)` para debug
4. **Tests automatizados** - Agrega aserciones automáticas (PASS/FAIL)

---

## 📚 Documentación Relacionada

- [UI_SYSTEM.md](docs/UI_SYSTEM.md) - Documentación completa del UI Builder
- [table-header-refactoring.md](docs/table-header-refactoring.md) - Sistema de headers
- [slot-to-parent-migration.md](docs/slot-to-parent-migration.md) - Migración slot→parent
