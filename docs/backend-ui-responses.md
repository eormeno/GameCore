# Formato de Respuestas del Backend para UI

**IMPORTANTE:** Todas las respuestas del backend deben estar indexadas por `_id` del componente, NO como un array simple.

## Formato Correcto (Indexado por _id)

```json
{
  "56155660": {
    "_id": 56155660,
    "type": "label",
    "parent": "screen",
    "_order": 1,
    "text": "Hello World"
  },
  "78923451": {
    "_id": 78923451,
    "type": "button",
    "parent": "screen",
    "_order": 2,
    "text": "Click Me"
  }
}
```

## Formato Incorrecto (Array Simple) ❌

```json
[
  {
    "_id": 56155660,
    "type": "label",
    "text": "Hello World"
  }
]
```

Este formato NO es válido porque requiere que el frontend itere el array para encontrar componentes por ID. El formato indexado permite acceso directo: `ui[componentId]`.

---

# Patrón de creación de un nuevo elemento

El front recibe lo siguiente:

```json
{
  "56155660": {
    "_id": 56155660,
    "type": "label",
    "parent": "screen",
    "_order": 1,
    "text": "Welcome",
    "style": "primary"
  }
}
```

**Campos obligatorios para creación:**
- `_id`: Identificador determinístico único del componente
- `type`: Tipo de componente (label, button, input, etc.)
- `parent`: Número o string indicando dónde será visualizado. Si es `null`, indica que el componente debe eliminarse
- `_order`: Ubicación relativa del componente dentro del contenedor (relativo a su parent)

El front debe buscar entre sus componentes si existe el `_id`. Si existe, se asume que se trata de una modificación de atributos, en ese caso, **no debe venir el campo `type`** en el JSON del objeto.

# Patrón de modificación de atributos de un elemento existente

El backend retorna **solo los componentes que cambiaron**, indexados por `_id`:

```json
{
  "56155660": {
    "_id": 56155660,
    "text": "Updated Text",
    "style": "success"
  },
  "78923451": {
    "_id": 78923451,
    "disabled": true
  }
}
```

**Reglas importantes:**
- **NO debe incluirse** el campo `type` (el componente ya existe en el frontend)
- Solo se envían los atributos que cambiaron
- Si viene `parent`, indica que el componente se movió a otro contenedor
- Si `parent` es explícitamente `null`, significa que el componente debe eliminarse
- Si viene `_order`, indica que cambió su posición dentro del contenedor

# Patrón de eliminación de un elemento

Para eliminar un componente, se envía con `parent: null`:

```json
{
  "56155660": {
    "_id": 56155660,
    "parent": null
  }
}
```

El frontend debe:
1. Buscar el componente por `_id`
2. Removerlo de su contenedor actual
3. Destruir el componente

---

# Ejemplos Completos

## Ejemplo 1: Carga inicial de UI

**Endpoint:** `GET /api/demo-ui`

**Respuesta:**
```json
{
  "10001": {
    "_id": 10001,
    "type": "screen",
    "parent": "root",
    "_order": 1,
    "name": "main_screen"
  },
  "10002": {
    "_id": 10002,
    "type": "label",
    "parent": 10001,
    "_order": 1,
    "name": "lbl_welcome",
    "text": "Welcome to the App",
    "style": "primary"
  },
  "10003": {
    "_id": 10003,
    "type": "button",
    "parent": 10001,
    "_order": 2,
    "name": "btn_submit",
    "text": "Submit",
    "enabled": true
  }
}
```

## Ejemplo 2: Respuesta de evento (actualización)

**Endpoint:** `POST /api/ui-event`

**Body:**
```json
{
  "component_id": 10003,
  "event": "click",
  "action": "submit_form"
}
```

**Respuesta (solo componentes modificados):**
```json
{
  "10002": {
    "_id": 10002,
    "text": "Form submitted successfully!",
    "style": "success"
  },
  "10003": {
    "_id": 10003,
    "enabled": false,
    "text": "Submitting..."
  }
}
```

**Nota:** No se incluye `type` porque los componentes ya existen en el frontend.

## Ejemplo 3: Crear nuevo componente dinámicamente

**Respuesta de evento que crea un nuevo componente:**
```json
{
  "10004": {
    "_id": 10004,
    "type": "label",
    "parent": 10001,
    "_order": 3,
    "name": "lbl_result",
    "text": "Operation completed",
    "style": "info"
  }
}
```

**Nota:** Se incluye `type` porque es un componente nuevo.

## Ejemplo 4: Eliminar componente

**Respuesta de evento que elimina un componente:**
```json
{
  "10004": {
    "_id": 10004,
    "parent": null
  }
}
```

---

# Validación del Formato

El formato indexado es obligatorio para:
- ✅ Permitir acceso directo por ID: `ui[componentId]`
- ✅ Evitar iteraciones innecesarias en el frontend
- ✅ Facilitar merge de cambios en el estado de la UI
- ✅ Mejorar performance de búsquedas y actualizaciones

**Recuerda:** El backend SIEMPRE debe retornar objetos indexados por `_id`, nunca arrays simples con elementos secuenciales `[0, 1, 2, ...]`.