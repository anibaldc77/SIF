---
id: WP-231-I3-REVIEW
title: WP-231 I3 Implementation Review
summary: Revisa el ciclo de vida, rotación atómica y detección de replay de autenticación persistente.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-231
tags:
  - security
  - persistent-authentication
  - replay
  - implementation-review
depends_on:
  - EG-419
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-231 I3 Implementation Review

## Alcance revisado

Se incorpora emisión, validación, rotación de validator, expiración absoluta, revocación y detección de replay.

## Hallazgos

- La rotación mantiene el selector y la expiración absoluta.
- El digest anterior funciona como precondición del compare-and-swap.
- La reutilización del validator anterior provoca revocación fail-closed.
- La implementación en memoria respeta el contrato funcional pero no reemplaza un store transaccional productivo.
- Todavía no existe restauración de sesión ni transporte HTTP.

## Riesgos

Los stores persistentes deben garantizar que `rotate()` sea atómico. Un `SELECT` seguido de `UPDATE` sin precondición sobre el digest permitiría aceptar validaciones concurrentes del mismo token.

## Decisión

El incremento es apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
