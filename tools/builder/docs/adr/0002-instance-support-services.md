---
id: ADR-BUILDER-0002-INSTANCE-SUPPORT-SERVICES
title: ADR 0002: Utilidades como servicios de instancia
summary: Str, Arr y Reflection se exponen como servicios de instancia, aunque no mantienen estado. Esto conserva la posibilidad de sustituirlos, decorarlos o inyectarlos sin introducir funciones globales ni clases estáticas.
status: Draft for Review
version: 0.1.0
category: Architecture Decision Record
document_class: GovernanceDocument
authors:
  - SIF Team
created: 2026-07-15
updated: 2026-07-22
tags:
  - 0002
  - utilidades
  - como
  - servicios
  - instancia
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# ADR 0002: Utilidades como servicios de instancia

## Decisión

`Str`, `Arr` y `Reflection` se exponen como servicios de instancia, aunque no mantienen estado. Esto conserva la posibilidad de sustituirlos, decorarlos o inyectarlos sin introducir funciones globales ni clases estáticas.

## Consecuencia

Los consumidores reciben explícitamente la dependencia que utilizan y el comportamiento es testeable de forma aislada.
