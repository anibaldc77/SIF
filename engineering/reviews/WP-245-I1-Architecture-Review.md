---
id: WP-245-I1-REVIEW
title: WP-245 I1 Architecture Review
summary: Revisa la arquitectura inicial de OpenID4VCI Credential Issuance sobre la plataforma de seguridad SIF.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-245
tags:
  - security
  - verifiable-credentials
  - openid4vci
  - architecture-review
depends_on:
  - EG-529
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-245 I1 Architecture Review

## Alcance revisado

Se incorporan Credential Offer, Issuance Request, Issuance Response, Issuance Context y contratos iniciales de parsing, issuance, proof validation y configuration.

## Hallazgos

- Issuance queda separada de presentation/verifier responsibilities de WP-244.
- OAuth se reutiliza y no se reimplementa.
- Deferred issuance queda contemplada desde la arquitectura base.
- El access token se representa por referencia y no como secreto almacenado en el modelo.
- Wallet, HTTP, formatos y criptografía permanecen fuera de Foundation.

## Decisión

Apto para I2 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
