---
id: EG-529
title: OpenID4VCI Credential Issuance Architecture
summary: Define modelos y contratos neutrales para ofertas, solicitudes, respuestas y contexto de emisión de credenciales verificables sobre una API protegida por OAuth.
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
  - issuance
depends_on:
  - EG-528
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-529 — OpenID4VCI Credential Issuance Architecture

## Objetivo

Introducir una arquitectura contract-first para emisión de credenciales verificables, reutilizando OAuth y las capacidades de credenciales de WP-244.

## Credential Offer

`CredentialOffer` representa:

- Credential Issuer;
- credential configuration ids;
- grant types;
- issuer state opcional.

## Issuance Request

`CredentialIssuanceRequest` representa:

- credential configuration id;
- proof;
- credential identifier opcional.

## Issuance Response

`CredentialIssuanceResponse` soporta:

- credencial inmediata;
- transaction id para deferred issuance;
- c_nonce opcional.

## Issuance Context

`CredentialIssuanceContext` representa issuer, subject, client y una referencia opcional al access token.

Foundation no almacena el token bruto.

## Contratos

- `CredentialOfferParserInterface`;
- `CredentialIssuanceServiceInterface`;
- `CredentialIssuanceProofValidatorInterface`;
- `CredentialConfigurationProviderInterface`.

## Neutralidad

Foundation no conoce wallet, HTTP framework, endpoint concreto, formato de credencial, librería JWT/COSE, almacenamiento o issuer externo.

## Roadmap I1-I8

1. I1 — architecture, offers y issuance contracts;
2. I2 — authorization code y pre-authorized code grants;
3. I3 — proof-of-possession, c_nonce y replay protection;
4. I4 — credential endpoint, batch y deferred issuance;
5. I5 — issuer metadata y credential configuration discovery;
6. I6 — authorization details y transaction binding;
7. I7 — status lifecycle, notifications y operational readiness;
8. I8 — product completion e integration tests.

## Criterios de aceptación

Modelos inmutables, contratos tipados, neutralidad de transporte/wallet/formato, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
