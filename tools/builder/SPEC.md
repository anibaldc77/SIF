---
id: BUILDER-SPEC
title: Especificación: Support Library
summary: Support no depende de componentes SIF. Sus value objects validan datos al crearse y sus colecciones evitan mutaciones de la instancia original. Los fallos se comunican con excepciones específicas; ningún método usa false como señal de error.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-07-15
updated: 2026-07-22
tags:
  - especificaci
  - support
  - library
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# Especificación: Support Library

Support no depende de componentes SIF. Sus value objects validan datos al crearse y sus colecciones evitan mutaciones de la instancia original. Los fallos se comunican con excepciones específicas; ningún método usa `false` como señal de error.

Las utilidades son servicios de instancia para mantener sustituibilidad. `Stopwatch` representa explícitamente el ciclo de inicio/parada y produce un `Timer` inmutable.

`Path` convierte separadores a `/`, resuelve `.` y `..`, y rechaza intentos de escapar de una raíz relativa.
