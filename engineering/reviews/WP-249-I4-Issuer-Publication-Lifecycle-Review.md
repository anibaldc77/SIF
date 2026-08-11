---
id: WP-249-I4-REVIEW
title: WP-249 I4 Issuer Publication Lifecycle Review
summary: Revisa allocation, lifecycle y publication boundaries del issuer para credential status.
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
  - issuer
  - publication
  - lifecycle
  - architecture-review
depends_on:
  - EG-564
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-249 I4 Issuer Publication Lifecycle Review

## Alcance revisado

Se incorporan lifecycle states/transitions, status allocation, publication plan/result y contracts separados para repository, lifecycle policy, planner y publisher.

## Hallazgos

- Allocation no queda acoplada a persistence concreta.
- Lifecycle distingue suspensión reversible de revocación terminal.
- Publication planning queda separado de publicación efectiva.
- La versión publicada es explícita.
- Locking, scheduling y transport permanecen fuera de Foundation.

## Compatibilidad

I4 no modifica contracts preexistentes ni altera el resolver legado.

## Decisión

Apto para I5 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
