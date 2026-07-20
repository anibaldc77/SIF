---
id: EG-009
title: Reference Resolution
work_package: WP-102
status: Draft
version: 0.1.0
---

# EG-009 — Reference Resolution

## 1. Objetivo

Resolver las referencias normalizadas producidas por EG-008 contra el `RepositoryIndex` de WP-101, conservando resultados deterministas y diagnósticos estructurados para destinos inexistentes.

## 2. Alcance

Este incremento incorpora:

- contrato `ReferenceResolverInterface`;
- implementación `ReferenceResolver`;
- resultados `ResolvedReference` y `BrokenReference`;
- agregado inmutable `ResolutionResult`;
- métricas `ResolutionStatistics`;
- resolución por identificador exacto contra `RepositoryIndex`;
- detección de destinos inexistentes.

Quedan fuera de alcance:

- extracción de referencias desde documentos, cubierta por EG-008;
- validación de existencia del documento fuente;
- detección de ciclos;
- construcción de grafos;
- análisis de impacto;
- escritura de informes o documentación;
- referencias ambiguas. `RepositoryIndex` garantiza unicidad y rechaza duplicados al construirse.

## 3. Reglas

1. Una referencia queda resuelta cuando `RepositoryIndex::get()` devuelve una entrada para su identificador destino.
2. Una referencia queda rota con razón `target_not_found` cuando el destino no existe.
3. La inexistencia del origen no altera la resolución del destino; la validación del origen pertenece a otra responsabilidad.
4. Los resultados se ordenan por `Reference::identity()` para garantizar determinismo.
5. `ResolvedReference` debe rechazar una entrada cuyo identificador no coincida con el destino de la referencia.
6. Un resultado sin referencias se considera exitoso y posee tasa de resolución `1.0`.

## 4. Contratos

```php
interface ReferenceResolverInterface
{
    public function resolve(ReferenceCollection $references, RepositoryIndex $index): ResolutionResult;
}
```

`ResolutionResult` expone colecciones separadas de referencias resueltas y rotas, contadores y estado global.

## 5. Decisiones de diseño

La resolución no modifica `Reference`, `ReferenceCollection` ni `RepositoryIndex`. El subsistema opera como una capa de aplicación pura sobre modelos existentes.

No se introduce un estado «ambiguo» porque el índice vigente representa cada identificador con una única entrada y lanza `DuplicateRepositoryEntryException` ante duplicados. Modelar ambigüedad en esta etapa produciría un estado imposible.

## 6. Criterios de aceptación

- destinos existentes se resuelven con su `RepositoryIndexEntry`;
- destinos ausentes producen `BrokenReference`;
- la salida es determinista;
- se preservan tipo, origen, contexto y ubicación de la referencia original;
- las estadísticas concuerdan con el resultado;
- PHPUnit y PHPStan nivel 8 finalizan sin errores.
