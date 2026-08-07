---
id: WP-233-I6-REVIEW
title: WP-233 I6 Implementation Review
summary: Revisa mapping explícito de scopes OAuth a permissions y adaptación de claims al modelo de autorización avanzada.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-233
tags:
  - security
  - oauth2
  - scopes
  - permissions
  - implementation-review
depends_on:
  - EG-438
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-233 I6 Implementation Review

## Alcance revisado

Se incorpora mapping explícito scope→permission, un resolver compatible con WP-232 y claims namespaced como atributos de autorización.

## Hallazgos

- Los scopes no se convierten automáticamente en permissions.
- El subject del token debe coincidir con el principal.
- Los claims se namespacean.
- No se crea otro tipo de decisión.
- La integración no muta autenticación.

## Riesgo evitado

Tratar scopes como permissions por identidad podría conceder capacidades no previstas cuando un Authorization Server emite scopes con semántica distinta a la aplicación.

## Decisión

El incremento es apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
