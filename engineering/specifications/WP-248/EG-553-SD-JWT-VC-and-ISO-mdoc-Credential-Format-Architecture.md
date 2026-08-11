---
id: EG-553
title: SD-JWT VC and ISO mdoc Credential Format Architecture
summary: Define version-aware adapter boundaries for SD-JWT VC and ISO mdoc credential processing while reusing the generic Verifiable Credentials contracts introduced by WP-244.
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
  - mdoc
  - credential-format
depends_on:
  - EG-552
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-553 — SD-JWT VC and ISO mdoc Credential Format Architecture

## Objetivo

Implementar fronteras explícitas para formatos de alta garantía sin introducir JOSE, CBOR, COSE, trust stores o vendors concretos en Foundation.

## Continuidad arquitectónica

WP-244 definió `CredentialFormat`, `CredentialEnvelope`, `CredentialFormatHandlerInterface` y límites de trust neutrales a formato.

WP-248 especializa esos límites para SD-JWT VC e ISO mdoc mediante adapters y perfiles versionados.

## Formatos

`HighAssuranceCredentialFormat` reconoce:

- `dc+sd-jwt`;
- `mso_mdoc`.

## Versionado

`CredentialFormatProfile` mantiene el identificador de versión/perfil separado del formato.

Esto es obligatorio para permitir evolución de especificaciones sin romper contratos públicos.

## Processing Context

`CredentialFormatProcessingContext` representa:

- instante de evaluación;
- trusted issuer identifiers;
- required claims;
- holder binding requirement;
- status validation requirement.

## Assessment

`CredentialFormatProcessingAssessment` separa:

- validez global;
- issuer trust;
- signature validity;
- claims validity;
- holder binding validity;
- violations;
- warnings.

## Contratos

- `SdJwtVcCredentialProcessorInterface`;
- `IsoMdocCredentialProcessorInterface`;
- `HighAssuranceCredentialFormatPolicyInterface`.

## Neutralidad

Foundation no implementa JWT/JWS, SD-JWT disclosure cryptography, KB-JWT, CBOR, COSE, MSO validation, X.509 path building, HTTP o persistence concreta.

## Roadmap WP-248

1. I1 — architecture y version-aware format contracts;
2. I2 — SD-JWT VC data model y disclosure boundaries;
3. I3 — SD-JWT VC issuer trust, status y key binding;
4. I4 — ISO mdoc namespace/data element model y device response boundaries;
5. I5 — ISO mdoc issuer authentication, MSO y device authentication;
6. I6 — OpenID4VCI/OpenID4VP format interoperability adapters;
7. I7 — high-assurance profile, privacy y operational readiness;
8. I8 — Product Completion e integración.

## Criterios de aceptación

Contratos tipados, versión/perfil desacoplados del formato, reutilización de WP-244, neutralidad criptográfica, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
