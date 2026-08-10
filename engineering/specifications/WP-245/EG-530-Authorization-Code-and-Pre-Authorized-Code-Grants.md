---
id: EG-530
title: Authorization Code and Pre Authorized Code Grants
summary: Define modelos y contratos para Authorization Code y Pre-Authorized Code grants de OpenID4VCI reutilizando OAuth y manteniendo Foundation desacoplado del authorization server y transporte.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-245
tags:
  - security
  - verifiable-credentials
  - openid4vci
  - grants
depends_on:
  - EG-529
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-530 — Authorization Code and Pre-Authorized Code Grants

## Objetivo

Modelar los grants principales de OpenID4VCI sin reimplementar OAuth.

## Grant Type

`CredentialIssuanceGrantType` representa:

- authorization_code;
- pre-authorized_code.

## Authorization Code

`AuthorizationCodeIssuanceGrant` expresa:

- authorization code;
- client id;
- redirect URI opcional;
- PKCE code verifier opcional.

## Pre-Authorized Code

`PreAuthorizedCodeIssuanceGrant` expresa:

- pre-authorized code;
- transaction code opcional.

## Grant Context

`CredentialIssuanceGrantContext` representa:

- credential issuer;
- client id;
- subject id opcional;
- issuer state opcional.

## Assessment

`CredentialIssuanceGrantAssessment` separa:

- valid;
- violations;
- warnings.

## Contratos

- `AuthorizationCodeIssuanceGrantValidatorInterface`;
- `PreAuthorizedCodeIssuanceGrantValidatorInterface`;
- `CredentialIssuanceGrantResolverInterface`.

## Seguridad

Implementaciones productivas deberán:

- validar expiration y single use;
- validar PKCE cuando corresponda;
- aplicar límites de intentos para transaction code;
- vincular issuer state con el flujo correcto;
- impedir sustitución de client;
- no registrar códigos o transaction codes completos.

## Neutralidad

Foundation no conoce authorization endpoint, token endpoint, HTTP framework, storage, Redis o implementación OAuth concreta.

## Criterios de aceptación

Grant models/context/assessment tipados, validators separados, reutilización de OAuth, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
