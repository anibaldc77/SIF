---
id: EG-585
title: OpenID4VC HAIP Architecture and Conformance Boundaries
summary: Define the OpenID4VC High Assurance Interoperability Profile 1.0 composition and conformance boundaries over the existing SIF issuance, presentation, credential-format, FAPI and trust subsystems.
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
  - high-assurance
  - interoperability
depends_on:
  - EG-584
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-585 — OpenID4VC HAIP Architecture and Conformance Boundaries

## Objetivo

Introducir OpenID4VC High Assurance Interoperability Profile 1.0 como una capa de composición y conformance sobre capacidades ya existentes en SIF.

WP-252 no reimplementa OpenID4VCI, OpenID4VP, FAPI 2.0, SD-JWT VC, ISO mdoc, credential status ni credential trust.

## Flows

`OpenId4VcHaipFlow` representa:

- issuance;
- presentation via redirect;
- presentation via Digital Credentials API.

Una implementación puede seleccionar los flows que ofrece, pero debe cumplir los requisitos completos del profile para cada flow seleccionado.

## Credential Formats

`OpenId4VcHaipCredentialFormat` representa los dos format profiles base:

- IETF SD-JWT VC;
- ISO mdoc.

## Profile

`OpenId4VcHaipProfile` agrupa flows y credential formats habilitados por un deployment/ecosystem.

## Conformance Context

`OpenId4VcHaipConformanceContext` conserva:

- profile;
- available capabilities;
- active security controls.

La evaluación concreta permanece detrás de `OpenId4VcHaipConformancePolicyInterface`.

## Existing SIF Boundaries

HAIP compone capacidades de:

- WP-242 FAPI 2.0;
- WP-245 OpenID4VCI;
- WP-247 OpenID4VP;
- WP-248 SD-JWT VC / ISO mdoc;
- WP-249 Credential Status;
- WP-250 Credential Trust;
- WP-244 generic High Assurance Interoperability.

WP-251 OpenID Federation puede utilizarse como mecanismo ecosystem-specific de trust/discovery, pero no es requisito intrínseco de HAIP.

## Generic High-Assurance Bridge

`OpenId4VcHaipInteroperabilityBridgeInterface` permite mapear el contexto HAIP al contexto genérico `HighAssuranceInteroperabilityContext` de WP-244.

## Neutralidad

Foundation no implementa HTTP, JWT/JWS libraries, platform wallet APIs, HSM/KMS ni crypto provider concreto.

## Roadmap WP-252

1. I1 — HAIP architecture and conformance boundaries;
2. I2 — HAIP OpenID4VCI authorization, PAR, PKCE and DPoP profile;
3. I3 — Wallet Attestation and wallet-authentication boundaries;
4. I4 — Credential endpoint proof, Key Attestation and key-binding profile;
5. I5 — HAIP OpenID4VP redirect and Digital Credentials API presentation profile;
6. I6 — SD-JWT VC / ISO mdoc crypto, status and trust conformance;
7. I7 — Ecosystem policy, privacy, conformance evidence and operational readiness;
8. I8 — Product completion and release readiness.
