---
id: EG-547
title: OpenID4VP Request Object Request URI and Verifier Authentication
summary: Define request objects, request URI resolution and verifier authentication boundaries for OpenID4VP.
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
  - request-object
  - request-uri
  - verifier-authentication
depends_on:
  - EG-546
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-547 — OpenID4VP Request Object, Request URI and Verifier Authentication

## Objetivo

Formalizar los límites de seguridad para authorization requests inline, request objects y request URI, manteniendo separadas resolución, verificación criptográfica y política de confianza del verifier.

## Request Source

`OpenId4VpAuthorizationRequestSource` distingue parámetros inline, request object y request URI.

## Request Object

`OpenId4VpRequestObject` transporta representación serializada y hints criptográficos sin implementar JOSE dentro del modelo.

## Request URI

`OpenId4VpRequestUri` representa una URI resoluble. La resolución se delega a `OpenId4VpRequestUriResolverInterface`.

## Verifier Authentication

`OpenId4VpRequestObjectVerifierInterface` produce un `OpenId4VpVerifierAuthenticationResult`.

`OpenId4VpVerifierAuthenticationPolicyInterface` decide si el resultado satisface la política local de confianza.

## Requisitos de seguridad

- request URI resolution debe aplicar controles SSRF fuera del modelo;
- la verificación criptográfica debe validar firma, algoritmo, key binding y freshness según policy;
- la identidad autenticada del verifier debe permanecer separada de la decisión de aceptación;
- Foundation no implementa HTTP, JOSE ni discovery concretos.

## Criterios de aceptación

Modelos y contratos tipados, separación entre resolución/verificación/policy, neutralidad de transporte y librería criptográfica, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
