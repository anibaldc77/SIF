---
id: WP-249-I5-REVIEW
title: WP-249 I5 Verifier Caching Freshness Review
summary: Revisa cache, freshness, refresh y failure-policy boundaries para resolución verifier-side de credential status.
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
  - credential-status
  - verifier
  - caching
  - freshness
  - architecture-review
depends_on:
  - EG-565
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-249 I5 Verifier Caching Freshness Review

## Alcance revisado

Se incorporan cache entry, resolution decision, failure modes y contratos independientes de cache, refresh policy y failure policy.

## Hallazgos

- Freshness y stale usability son ventanas distintas.
- Stale use no queda habilitado implícitamente.
- Fail-closed puede imponerse por perfil.
- Cache storage y transport permanecen fuera de Foundation.
- Refresh queda modelado como policy y no como scheduler.

## Compatibilidad

No se modifican contracts públicos de I1-I4.

## Decisión

Apto para I6 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
