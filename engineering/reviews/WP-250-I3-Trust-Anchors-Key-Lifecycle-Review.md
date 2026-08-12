---
id: WP-250-I3-REVIEW
title: WP-250 I3 Trust Anchors Key Lifecycle Review
summary: Revisa trust anchors, key-material status y lifecycle transition boundaries para credential trust.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-12
updated: 2026-08-12
work_package: WP-250
tags:
  - security
  - verifiable-credentials
  - trust
  - trust-anchor
  - key-lifecycle
  - architecture-review
depends_on:
  - EG-571
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-250 I3 Trust Anchors Key Lifecycle Review

## Alcance revisado
I3 incorpora trust-anchor status/model, key-material status/model, lifecycle transition y contracts separados para resolution y policy.

## Hallazgos
- Trust-anchor lifecycle y key lifecycle permanecen responsabilidades distintas.
- Key rotation es explícita y no se confunde con revocation.
- Validity intervals quedan representados en ambos modelos.
- PKI/JWKS/HSM/KMS concretos permanecen fuera de Foundation.
- Contracts de I1/I2 permanecen intactos.

## Decisión
Apto para I4 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
