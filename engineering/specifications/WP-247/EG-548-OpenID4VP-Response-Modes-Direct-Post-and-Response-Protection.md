---
id: EG-548
title: OpenID4VP Response Modes Direct Post and Response Protection
summary: Define response modes, destinations, direct post transport boundaries and protected authorization responses for OpenID4VP without coupling Foundation to HTTP or JOSE implementations.
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
  - response-modes
  - direct-post
  - response-protection
depends_on:
  - EG-547
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-548 — OpenID4VP Response Modes, Direct Post and Response Protection

## Objetivo

Formalizar response modes y protección de Authorization Responses sin implementar transporte HTTP o JOSE dentro de Foundation.

## Response Modes

`OpenId4VpResponseMode` distingue:

- query;
- fragment;
- direct_post;
- direct_post.jwt.

## Response Destination

`OpenId4VpResponseDestination` vincula URI y response mode.

## Protected Response

`OpenId4VpProtectedAuthorizationResponse` representa la respuesta serializada protegida y headers asociados.

## Protection Context

`OpenId4VpResponseProtectionContext` vincula verifier, nonce, state y transaction id.

## Assessment

`OpenId4VpResponseProtectionAssessment` separa verifier binding, nonce, state y transaction binding.

## Contratos

- `OpenId4VpAuthorizationResponseProtectorInterface`;
- `OpenId4VpProtectedAuthorizationResponseValidatorInterface`;
- `OpenId4VpResponseTransportInterface`;
- `OpenId4VpResponseDestinationPolicyInterface`.

## Seguridad

Implementaciones productivas deberán validar destination, impedir redirect/substitution attacks, proteger payload cuando la policy lo requiera, validar nonce/state/transaction binding y tratar transporte y protección criptográfica como responsabilidades separadas.

## Neutralidad

Foundation no conoce HTTP client, JOSE implementation, framework web o storage concreto.

## Criterios de aceptación

Modes/destination/protected response/context/assessment tipados, contratos separados, neutralidad HTTP/JOSE, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
