---
id: EG-586
title: HAIP OpenID4VCI Authorization PAR PKCE and DPoP Profile
summary: Define the HAIP 1.0 conformance profile for OpenID4VCI authorization code, PAR, PKCE S256, authorization-response issuer validation, scope binding and DPoP sender-constrained tokens.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
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
depends_on:
  - EG-585
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-586 — HAIP OpenID4VCI Authorization, PAR, PKCE and DPoP Profile

## Objetivo

Definir las restricciones HAIP aplicables al authorization layer de OpenID4VCI sin reimplementar OAuth, FAPI ni credential issuance.

## Authorization Code

Wallet y Credential Issuer deben soportar Authorization Code.

## FAPI 2.0

El flow debe reutilizar la implementación FAPI 2.0 de SIF y exigir sus requisitos aplicables.

## PKCE

PKCE es obligatorio y el code challenge method es `S256`.

## PAR

PAR es obligatorio cuando se utiliza Authorization Endpoint.

La ausencia de Authorization Endpoint no obliga a representar PAR como utilizado.

## Authorization Response Issuer

El `iss` de la Authorization Response debe validarse conforme al perfil aplicable.

## DPoP

Sender-constrained access tokens deben soportar DPoP.

El Wallet debe poder procesar el mecanismo de DPoP Nonce requerido por los endpoints aplicables.

## Credential Type Scope

Cuando se utiliza Authorization Endpoint, `scope` comunica Credential Type(s) y debe existir una asociación válida entre scope y Credential Type.

## Contratos

- `OpenId4VcHaipAuthorizationConformancePolicyInterface`;
- `OpenId4VcHaipAuthorizationRequirementsProviderInterface`.

## Reutilización

I2 compone:

- FAPI 2.0 de WP-242;
- OpenID4VCI de WP-245;
- HAIP base de I1.

No introduce un OAuth engine paralelo.

## Neutralidad

Foundation no implementa HTTP, token transport, DPoP proof signing, JWT/JWS library, persistence ni crypto concreto.

## Compatibilidad

I2 agrega conformance rules sin modificar WP-242, WP-245 ni I1.
