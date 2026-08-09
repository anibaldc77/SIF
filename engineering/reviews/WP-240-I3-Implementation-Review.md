---
id: WP-240-I3-REVIEW
title: WP-240 I3 Implementation Review
summary: Revisa JWT Secured Authorization Requests, verificación y replay boundaries.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-240
tags:
  - security
  - oauth
  - jar
  - implementation-review
depends_on:
  - EG-491
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-240 I3 Implementation Review

## Alcance revisado

Se incorpora:

- authorization request object;
- verification result;
- signer contract;
- verifier contract;
- replay store;
- excepción específica.

## Hallazgos

- JAR reutiliza `OAuthAuthorizationRequest`.
- La representación firmada no obliga librería JWT concreta.
- Lifetime, issuer, audience y jti son explícitos.
- Replay protection permanece detrás de contrato.
- HTTP y JWKS publication quedan fuera de Foundation.

## Decisión

El incremento es apto para continuar a I4 cuando PHPUnit, PHPStan y Builder finalicen sin errores.
