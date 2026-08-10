---
id: WP-242-I4-REVIEW
title: WP-242 I4 Implementation Review
summary: Revisa sender-constrained token policy para DPoP y mTLS bajo FAPI 2.0.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-242
tags:
  - security
  - oauth
  - fapi
  - dpop
  - mtls
  - implementation-review
depends_on:
  - EG-508
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-242 I4 Implementation Review

## Alcance revisado

Se incorporan sender constraint methods, requirements, context, assessment y policies independientes para DPoP y mTLS.

## Hallazgos

- DPoP se reutiliza desde WP-240.
- mTLS se modela como policy de binding y no como implementación TLS.
- La capa FAPI evita bearer downgrade.
- Client y resource quedan explícitos en el contexto.
- Criptografía, TLS termination y storage permanecen fuera de Foundation.

## Decisión

Apto para I5 cuando PHPUnit, PHPStan y Builder finalicen sin errores.
