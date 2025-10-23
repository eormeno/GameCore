# Patrón de creación de un nuevo elemento.
El front recibe lo siguiente:

```
[
  {
    '_id': (un identificador determinístico)
    'type': (el tipo de componente)
    'parent': (un número o un string indicando en dónde será visualizado. En caso de traer null, indicará que el componente se debe eliminar de su contenedor),
    '_order': (La ubicación relativa del componente dentro del contenedor)
  }
]
```

El front debe buscar entre sus componentes si existe el '_id'. Si existe, se asume que se trata de una modificación de atributos, en ese caso, se ignora type, por lo que no debería venir en el Json del objeto.

# Patrón de modificación de atributos de un elemento existente en el front

```
[
  {
    '_id': (el identificador determinístico del elemento que se debe modificar
    'parent': (si viene este atributo, indica que el componente se movió a otro contenedor. Si el valor es explícitamente null, significa que el componente se debe eliminar del front),
    '_order': (Si llega este atributo, significa que el componente modificó su orden en el contenedor)
  }
]
```

# Patrón de eliminación de un elementos

```
[
  {
    '_id': (el identificador determinístico del elemento que se debe modificar
    'parent': null,
  }
]
```