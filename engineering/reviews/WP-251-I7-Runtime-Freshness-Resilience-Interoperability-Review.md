---
id: WP-251-I7-REVIEW
title: WP-251 I7 Runtime Freshness Resilience Interoperability Review
summary: Revisa freshness, controlled stale fallback y OIDC/wallet interoperability readiness del runtime OpenID Federation.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-13
updated: 2026-08-13
work_package: WP-251
tags:
  - security
  - federation
  - openid-federation
  - freshness
  - resilience
  - interoperability
  - architecture-review
depends_on:
  - EG-583
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-251 I7 Runtime Freshness Resilience Interoperability Review

## Alcance revisado

I7 incorpora runtime evidence, freshness status/policy, failure policy, interoperability profile y runtime readiness report/evaluator.

## Hallazgos

- Fresh, stale-usable y expired quedan diferenciados.
- Fail-closed es el comportamiento por defecto.
- Stale evidence requiere opt-in explícito.
- Expired evidence nunca se reutiliza.
- OIDC/OpenID4VCI/OpenID4VP/wallet interoperability queda representada sin acoplarse a transports.
- High-assurance enforcement continúa perteneciendo a WP-250.

## Compatibilidad

I7 no modifica I1-I6 ni WP-250.

## Decisión

Apto para I8 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
