---
id: EG-545
title: OpenID4VP Presentation Protocol Architecture
summary: Define modelos y contratos neutrales para requests, responses, vp_token, presentation queries y binding de transacción OpenID4VP reutilizando la capa de Verifiable Credentials existente.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
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
depends_on:
  - EG-544
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-545 — OpenID4VP Presentation Protocol Architecture

## Objetivo

Agregar la capa protocolaria OpenID4VP sobre la infraestructura de Verifiable Credentials existente sin duplicar verificación criptográfica ni formatos de credencial.

## Authorization Request

`OpenId4VpAuthorizationRequest` representa:

- client id;
- nonce;
- response type;
- response mode opcional;
- response URI opcional;
- presentation query;
- metadata opcional.

## Authorization Response

`OpenId4VpAuthorizationResponse` representa:

- uno o más VP Tokens;
- state opcional;
- metadata opcional.

## Presentation Context

`OpenId4VpPresentationContext` vincula:

- verifier id;
- nonce;
- expected origins;
- wallet nonce opcional;
- transaction id opcional.

## Assessment

`OpenId4VpPresentationAssessment` separa:

- valid;
- nonce validity;
- verifier binding;
- presentation query satisfaction;
- violations;
- warnings.

## Contratos

- `OpenId4VpAuthorizationRequestValidatorInterface`;
- `OpenId4VpAuthorizationResponseValidatorInterface`;
- `OpenId4VpPresentationQueryValidatorInterface`;
- `OpenId4VpVpTokenResolverInterface`.

## Seguridad

Implementaciones productivas deberán validar nonce, verifier/client binding, response destination, transaction binding, expected origin cuando corresponda y evitar replay.

## Neutralidad

Foundation no conoce HTTP client, Digital Credentials API, browser transport, SD-JWT VC, ISO mdoc, storage o librería criptográfica concreta.

## Roadmap I1-I8

1. I1 — protocol architecture y capability contracts;
2. I2 — presentation query y credential selection;
3. I3 — request object, request URI y verifier authentication;
4. I4 — response modes, direct post y response protection;
5. I5 — VP Token processing y presentation submission;
6. I6 — Digital Credentials API transport profile;
7. I7 — transaction data, privacy y operational readiness;
8. I8 — Product Completion e integración.

## Criterios de aceptación

Modelos inmutables, contratos tipados, reutilización de WP-244, neutralidad de formato/transporte, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
