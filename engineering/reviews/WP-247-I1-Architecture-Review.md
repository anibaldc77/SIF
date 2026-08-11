---
id: WP-247-I1-REVIEW
title: WP-247 I1 Architecture Review
summary: Revisa la arquitectura inicial de OpenID4VP Presentation Protocol sobre la plataforma de Verifiable Credentials de SIF.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-247
tags:
  - security
  - verifiable-credentials
  - openid4vp
  - presentation
  - architecture-review
depends_on:
  - EG-545
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-247 I1 Architecture Review

## Alcance revisado

Se incorporan authorization request/response, presentation context/assessment y contratos iniciales de request validation, response validation, query validation y VP Token resolution.

## Hallazgos

- OpenID4VP queda separado de la verificación criptográfica de credenciales.
- WP-244 continúa siendo la capa de credential/presentation verification.
- Nonce, verifier binding y transaction binding son inputs explícitos de seguridad.
- VP Token permanece neutral respecto del formato concreto.
- HTTP y Digital Credentials API quedan fuera de Foundation.

## Decisión

Apto para I2 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
