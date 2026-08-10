---
id: WP-241-I3-REVIEW
title: WP-241 I3 Implementation Review
summary: Revisa OAuth Protected Resource Metadata, builder, exact resource validation y signed metadata boundaries.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-09
updated: 2026-08-09
work_package: WP-241
tags:
  - security
  - oauth
  - protected-resource
  - implementation-review
depends_on:
  - EG-499
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-241 I3 Implementation Review

## Alcance revisado

Se incorpora:

- Protected Resource Metadata extendida;
- JWKS/scopes/bearer methods;
- resource signing algorithms;
- human-readable resource metadata;
- mTLS-bound token capability;
- RAR metadata;
- DPoP metadata;
- signed metadata;
- builder persistente;
- serializer/validator/URI resolver/verifier contracts.

## Hallazgos

- Se conserva compatibilidad con el constructor introducido en I1.
- La validación exacta de `resource` puede implementarse sin acoplar Foundation a HTTP.
- DPoP, RAR y mTLS se describen sin duplicar su implementación.
- Signed metadata permanece detrás de un verifier.
- Networking y discovery físico permanecen fuera de Foundation.

## Decisión

El incremento es apto para continuar a I4 cuando PHPUnit, PHPStan y Builder finalicen sin errores.
