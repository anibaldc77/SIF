---
id: WP-233-I1-REVIEW
title: WP-233 I1 Architecture Review
summary: Revisa la arquitectura base del OAuth 2.0 Resource Server y sus contratos neutrales de access token.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-233
tags:
  - security
  - oauth2
  - resource-server
  - architecture-review
depends_on:
  - EG-433
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-233 I1 Architecture Review

## Alcance revisado

Se incorpora el vocabulario base de access token opaco, scopes, token validado y contratos de extracción y validación.

## Hallazgos

- El token se trata como secreto.
- El Core no presupone JWT.
- La validación queda detrás de contrato.
- La extracción Bearer queda separada de la validación.
- Los scopes todavía no se convierten en permisos.
- No existe lógica de emisión de tokens.

## Riesgo evitado

Acoplar el Resource Server directamente a JWT impediría soportar introspection u otros formatos opacos sin reescribir la API pública.

## Decisión

La arquitectura es apta para continuar hacia extracción Bearer y errores RFC 6750 en I2.
