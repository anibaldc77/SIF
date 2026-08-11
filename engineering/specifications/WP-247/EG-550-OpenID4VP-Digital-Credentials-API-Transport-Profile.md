---
id: EG-550
title: OpenID4VP Digital Credentials API Transport Profile
summary: Define a transport profile boundary for using OpenID4VP through the W3C Digital Credentials API without coupling Foundation to browser APIs or concrete user-agent implementations.
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
  - digital-credentials
  - browser-mediated
depends_on:
  - EG-549
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-550 — OpenID4VP Digital Credentials API Transport Profile

## Objetivo

Modelar una frontera de transporte compatible con Digital Credentials API sin acoplar Foundation al navegador o user agent.

## Request

`OpenId4VpDigitalCredentialsRequest` representa protocol, payload y origin opcional.

## Response

`OpenId4VpDigitalCredentialsResponse` representa protocol y payload devuelto por el transporte.

## Context

`OpenId4VpDigitalCredentialsContext` expresa origin, protocolos soportados y requirement de user mediation.

## Assessment

`OpenId4VpDigitalCredentialsAssessment` separa protocol support, origin validation y user mediation.

## Contratos

- `OpenId4VpDigitalCredentialsRequestFactoryInterface`;
- `OpenId4VpDigitalCredentialsResponseResolverInterface`;
- `OpenId4VpDigitalCredentialsPolicyInterface`;
- `OpenId4VpDigitalCredentialsTransportInterface`.

## Seguridad y privacidad

La implementación productiva deberá validar origin, protocolo esperado, user mediation y binding con la transacción OpenID4VP. El browser/user-agent conserva su responsabilidad de mediación y consentimiento.

## Neutralidad

Foundation no conoce `navigator.credentials`, DOM, HTML, implementation de browser, Android/iOS provider ni framework web concreto.

## Criterios de aceptación

Request/response/context/assessment tipados, contratos separados, neutralidad de Browser API, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
