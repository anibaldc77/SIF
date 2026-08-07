---
id: EG-418
title: Material de token y generación segura de autenticación persistente
summary: Define selector, validator, digest, valor de cookie sensible y generación criptográficamente segura para WP-231 I2.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-231
tags:
  - security
  - persistent-authentication
  - token
  - cookie
depends_on:
  - EG-417
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-418 — Material de token y generación segura de autenticación persistente

## Objetivo

Definir y proveer el material criptográfico necesario para credenciales persistentes utilizando el patrón selector + validator.

## Selector

El selector:

- es material público de búsqueda;
- puede viajar en la cookie;
- no autentica por sí mismo;
- debe tener entropía suficiente para evitar enumeración práctica;
- puede persistirse directamente;
- debe redactarse mediante fingerprint en snapshots y logs.

## Validator

El validator:

- es secreto;
- sólo existe en claro del lado cliente o durante una operación acotada;
- no puede serializarse ni clonarse;
- nunca debe persistirse en claro;
- se transforma mediante SHA-256 antes de almacenamiento;
- debe compararse mediante mecanismos resistentes a timing attacks en el flujo de validación posterior.

## Valor de cookie

El valor canónico transporta:

`selector.validator`

Su representación textual y de depuración queda redactada. La exposición del valor completo sólo puede realizarse mediante callback explícito.

## Generación

La implementación nativa usa `random_bytes()`.

Valores por defecto:

- selector: 128 bits;
- validator: 256 bits.

El validator se codifica Base64 URL-safe sin padding.

## Invariantes

- El servidor conserva sólo el digest del validator.
- No existe getter directo del validator en claro.
- El token completo no es serializable.
- El valor de cookie no es serializable.
- El digest es determinístico.
- Cada generación produce material independiente.

## Compatibilidad

No se modifican contratos de Session, Cookie, Authentication ni MFA. El transporte HTTP se incorporará en una implementación posterior.

## Criterios de aceptación

- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
