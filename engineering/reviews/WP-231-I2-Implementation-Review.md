---
id: WP-231-I2-REVIEW
title: WP-231 I2 Implementation Review
summary: Revisa generación segura, selector, validator, digest y material sensible de cookie para autenticación persistente.
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
  - token
  - implementation-review
depends_on:
  - EG-418
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-231 I2 Implementation Review

## Alcance revisado

Se incorpora generación criptográficamente segura de tokens persistentes, encapsulación sensible del validator, digest canónico y valor de cookie selector/validator.

## Hallazgos

- El selector permanece separado del validator.
- El validator no ofrece getter en claro.
- El token y la cookie no pueden serializarse.
- Los métodos de depuración permanecen redactados.
- El servidor dispone de un digest determinístico para persistencia futura.
- La generación usa entropía criptográfica nativa y codificación URL-safe.

## Riesgos

La mera existencia de selector y validator no constituye todavía autenticación. I3 deberá implementar almacenamiento de ciclo de vida, rotación atómica y detección de replay antes de integrar restauración de sesión.

## Decisión

El incremento es apto para integración cuando PHPUnit, PHPStan y Builder finalicen sin errores.
