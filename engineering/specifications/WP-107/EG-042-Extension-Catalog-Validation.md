# EG-042 — Extension Catalog Validation

## Estado

Aprobado para implementación — WP-107, Incremento 3.

## Objetivo

Validar que los identificadores de analizadores, generadores y reporteros de un perfil resuelto correspondan a extensiones registradas por SIF Builder antes de componer o ejecutar el pipeline.

## Alcance

Este incremento incorpora:

- catálogo inmutable de extensiones;
- catálogo predeterminado con las extensiones incorporadas actualmente;
- validación acumulativa de perfiles resueltos;
- diagnósticos gobernados para extensiones desconocidas;
- pruebas unitarias deterministas.

No incorpora:

- instanciación de extensiones;
- cambios en la CLI;
- carga dinámica de clases;
- políticas de repositorio;
- modificación del Engine.

## Contratos

### ExtensionCatalog

Mantiene tres listas ordenadas y sin duplicados:

- analyzers;
- generators;
- reporters.

El catálogo valida la forma de cada identificador al construirse. El mismo identificador puede existir en categorías diferentes porque cada categoría posee un espacio de nombres lógico independiente.

### BuildProfileExtensionValidator

Recibe un `ResolvedBuildProfile`, un `ExtensionCatalog` y la ruta opcional de origen. No modifica el perfil ni instancia extensiones.

Cuando todos los identificadores existen, devuelve el mismo perfil resuelto. Cuando existen identificadores desconocidos, devuelve todos los diagnósticos y no expone un perfil validado.

## Orden determinista

Los diagnósticos se producen en este orden:

1. analizadores;
2. generadores;
3. reporteros.

Dentro de cada categoría se conserva el orden declarado por el perfil resuelto.

## Diagnósticos

- `CONFIG-109`: analizador desconocido;
- `CONFIG-110`: generador desconocido;
- `CONFIG-111`: reportero desconocido.

Cada diagnóstico incluye como contexto:

- `profile`;
- `category`;
- `extension`.

## Seguridad

El catálogo contiene identificadores declarativos. No acepta nombres arbitrarios de clases, no ejecuta código y no realiza descubrimiento dinámico del sistema de archivos.

## Compatibilidad

`ExtensionCatalog::builtInDefault()` refleja la composición existente al finalizar WP-106: cinco analizadores, cinco generadores y dos reporteros.
