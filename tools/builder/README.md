---
id: BUILDER-README
title: SIF Support Library
summary: Biblioteca fundacional de SIF 2.0. Proporciona colecciones, value objects, utilidades inyectables y medición de tiempo, sin depender de otros componentes.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-07-15
updated: 2026-07-22
tags:
  - support
  - library
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# SIF Support Library

Biblioteca fundacional de SIF 2.0. Proporciona colecciones, value objects, utilidades inyectables y medición de tiempo, sin depender de otros componentes.

## Uso

Las dependencias se inyectan explícitamente. `Str`, `Arr` y `Reflection` son servicios de instancia, por lo que los consumidores no dependen de helpers globales ni de estado compartido.

`Version`, `Uuid`, `Path`, `Environment` y `JsonDocument` encapsulan sus respectivas invariantes. `ArrayCollection` ofrece una colección inmutable basada en valores.

```bash
composer install
composer test
```

Consulte [SPEC.md](SPEC.md) y [la guía de uso](docs/support-usage.md).
