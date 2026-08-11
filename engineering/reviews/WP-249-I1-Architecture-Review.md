---
id: WP-249-I1-REVIEW
title: WP-249 I1 Architecture Review
summary: Revisa la arquitectura inicial de credential status, revocation y suspension para SIF.
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
  - revocation
  - suspension
  - architecture-review
depends_on:
  - EG-561
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-249 I1 Architecture Review

## Alcance revisado

Se incorporan purpose, mechanism, version-aware profile, status reference, assessment y contracts separados para resolver, policy y freshness.

## Hallazgos

- Revocation y suspension quedan diferenciadas.
- El mecanismo queda desacoplado de la versión del profile.
- Bitstring Status List y Token Status List pueden coexistir.
- Freshness se modela como control separado.
- HTTP, compression, JWT, persistence y cache permanecen fuera de Foundation.

## Decisión

Apto para I2 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
