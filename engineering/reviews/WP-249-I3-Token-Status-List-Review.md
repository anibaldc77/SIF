---
id: WP-249-I3-REVIEW
title: WP-249 I3 Token Status List Review
summary: Revisa el data model, decoding boundary y status-value resolution para Token Status List.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-249
tags:
  - security
  - verifiable-credentials
  - token-status-list
  - status-resolution
  - architecture-review
depends_on:
  - EG-563
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-249 I3 Token Status List Review

## Alcance revisado

Se incorporan Token Status List reference/list/decoded data/value, deterministic resolver y contracts de decoder, authenticity verifier y value policy.

## Hallazgos

- El status list permanece separado del Status List Token que lo protege.
- Decoding y authenticity verification son responsabilidades distintas.
- La resolución opera sobre bytes ya decodificados y soporta valores multi-bit.
- TTL y aggregation URI quedan expresados sin introducir HTTP/cache.
- La revisión normativa externa permanece desacoplada mediante profile version.

## Compatibilidad

I3 no modifica `CredentialStatusResolverInterface` ni contracts públicos anteriores. Extiende WP-249 exclusivamente mediante nuevos tipos.

## Decisión

Apto para I4 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
