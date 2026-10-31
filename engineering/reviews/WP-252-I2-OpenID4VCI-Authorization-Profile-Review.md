---
id: WP-252-I2-REVIEW
title: WP-252 I2 OpenID4VCI Authorization Profile Review
summary: Revisa Authorization Code, PAR, PKCE S256, authorization-response issuer, scope binding y DPoP como restricciones HAIP sobre los subsistemas FAPI y OpenID4VCI existentes.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-13
updated: 2026-08-13
work_package: WP-252
tags:
  - security
  - verifiable-credentials
  - haip
  - openid4vci
  - fapi
  - dpop
  - architecture-review
depends_on:
  - EG-586
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-252 I2 OpenID4VCI Authorization Profile Review

## Alcance revisado

I2 incorpora requirements, context, assessment y policy HAIP para el authorization layer de issuance.

## Hallazgos

- Authorization Code es obligatorio.
- PKCE queda fijado a S256.
- PAR se exige cuando existe uso del Authorization Endpoint.
- Authorization Response issuer validation queda explícita.
- DPoP y DPoP Nonce support son requisitos separados.
- Scope-to-Credential-Type binding queda testable.
- FAPI 2.0 y OpenID4VCI permanecen en sus implementaciones canónicas.

## Compatibilidad

I2 no modifica WP-242, WP-245 ni I1.

## Decisión

Apto para I3 cuando PHPUnit, PHPStan y SIF Builder finalicen sin errores.
