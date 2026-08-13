---
id: WP-251-I3-REVIEW
title: WP-251 I3 Federation Protocol Messages Review
summary: Revisa request/response models y contracts para fetch, subordinate listing y resolve operations.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-12
updated: 2026-08-12
work_package: WP-251
tags:
  - security
  - federation
  - openid-federation
  - fetch
  - list
  - resolve
  - architecture-review
depends_on:
  - EG-579
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-251 I3 Federation Protocol Messages Review

## Alcance revisado

I3 incorpora requests, responses, protocol contracts y exceptions para fetch, list y resolve.

## Hallazgos

- Fetch modela la consulta superior-subordinado.
- Listing expone Immediate Subordinates y filtros sin acoplarse a HTTP.
- Resolve admite múltiples Trust Anchors y Entity Types.
- Resolved Metadata, Trust Chain y Trust Marks quedan representados separadamente.
- HTTP/JWT/JWS/crypto permanecen fuera de Foundation.

## Compatibilidad

I3 no modifica I1/I2 ni WP-250.

## Decisión

Apto para I4 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
