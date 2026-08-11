---
id: EG-555
title: SD-JWT VC Issuer Trust Status and Key Binding
summary: Define issuer trust, credential status and key binding boundaries for SD-JWT VC without coupling Foundation to discovery, trust stores, status transport or JWT verification libraries.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-248
tags:
  - security
  - verifiable-credentials
  - sd-jwt-vc
  - issuer-trust
  - credential-status
  - key-binding
depends_on:
  - EG-554
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-555 — SD-JWT VC Issuer Trust, Status and Key Binding

## Objetivo

Separar issuer trust, credential status y holder/key binding como controles independientes de SD-JWT VC.

## Issuer Trust

`SdJwtVcIssuerIdentity` representa identificador y atributos de confianza.

`SdJwtVcIssuerTrustAssessment` separa confianza global, validez del identificador y key material trust.

## Credential Status

`SdJwtVcCredentialStatus` representa tipo y referencia de status.

`SdJwtVcCredentialStatusAssessment` separa validez, revoked y suspended.

## Key Binding

`SdJwtVcKeyBindingAssessment` separa audience, nonce y holder key.

## Contratos

- `SdJwtVcIssuerTrustEvaluatorInterface`;
- `SdJwtVcCredentialStatusResolverInterface`;
- `SdJwtVcKeyBindingVerifierInterface`.

## Seguridad

La implementación productiva deberá validar issuer identifier, trust anchors, key rotation, freshness del status y audience/nonce/holder binding. Ninguno de estos controles implica por sí mismo la validez completa de la credencial.

## Neutralidad

Foundation no conoce discovery HTTP, JWKS fetch, trust store, status-list transport, JWT/JWS library, Redis o base de datos concreta.

## Criterios de aceptación

Issuer/status/key-binding tipados, contracts separados, neutralidad de infraestructura, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
