---
id: EG-521
title: Verifiable Credentials and High Assurance Identity Presentation Architecture
summary: Define modelos y contratos neutrales para credenciales verificables, presentaciones, selective disclosure e identity assurance sobre la plataforma de seguridad existente.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-244
tags:
  - security
  - verifiable-credentials
  - identity-assurance
  - openid4vp
depends_on:
  - EG-520
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-521 — Verifiable Credentials and High Assurance Identity Presentation Architecture

## Objetivo

Introducir una arquitectura contract-first para recibir, verificar y mapear credenciales y presentaciones de identidad de alta confianza.

## Base conceptual

WP-244 se apoya conceptualmente en:

- OpenID for Verifiable Presentations;
- OpenID Connect for Identity Assurance;
- Selective Disclosure JWT;
- formatos de credencial mediante adapters.

## Modelos

### VerifiableCredentialSubject

Representa subject id y claims.

### VerifiableCredential

Representa issuer, subject, tipos, vigencia y metadata.

### VerifiablePresentation

Representa holder, credentials, nonce y audience.

### IdentityAssuranceEvidence

Representa evidencia y método de verificación.

### VerifiedIdentityClaims

Representa claims ya mapeados a una superficie de identidad confiable.

## Contratos

- `VerifiablePresentationVerifierInterface`;
- `VerifiableCredentialVerifierInterface`;
- `IdentityAssuranceClaimsMapperInterface`;
- `SelectiveDisclosurePolicyInterface`.

## Neutralidad

Foundation no conoce:

- wallet concreta;
- SD-JWT implementation;
- mdoc;
- JSON-LD;
- JWT/JWS library;
- HSM;
- HTTP client;
- issuer concreto.

## Roadmap I1-I8

1. I1 — architecture y capability contracts;
2. I2 — presentation request/response y nonce/audience binding;
3. I3 — credential format adapters y trust validation boundaries;
4. I4 — selective disclosure y holder binding;
5. I5 — Identity Assurance verified claims/evidence;
6. I6 — credential status, revocation y freshness;
7. I7 — high-assurance interoperability/readiness;
8. I8 — product completion e integration tests.

## Criterios de aceptación

Modelos inmutables, contratos tipados, neutralidad de formato/crypto/wallet, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
