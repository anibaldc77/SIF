---
id: EG-558
title: OpenID4VCI OpenID4VP Credential Format Interoperability Adapters
summary: Define adapter boundaries that connect SD-JWT VC and ISO mdoc format profiles with existing OpenID4VCI issuance and OpenID4VP presentation protocol layers.
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
  - openid4vci
  - openid4vp
  - interoperability
depends_on:
  - EG-557
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-558 — OpenID4VCI/OpenID4VP Credential Format Interoperability Adapters

## Objetivo

Conectar los formatos SD-JWT VC e ISO mdoc con las capas protocolarias existentes de emisión y presentación sin duplicar modelos ni lógica de protocolo.

## Issuance Profile

`CredentialFormatIssuanceProfile` vincula un credential configuration id de emisión con un `CredentialFormatProfile`.

## Presentation Profile

`CredentialFormatPresentationProfile` vincula un requirement id de presentación con un `CredentialFormatProfile` y requested claims.

## Assessment

`CredentialFormatInteroperabilityAssessment` separa:

- compatibility global;
- format support;
- profile support;
- claims compatibility;
- violations;
- warnings.

## Contratos

- `OpenId4VciCredentialFormatAdapterInterface`;
- `OpenId4VpCredentialFormatAdapterInterface`;
- `CredentialFormatInteroperabilityPolicyInterface`.

## Principios arquitectónicos

Los adapters no recrean `CredentialIssuanceRequest`, `OpenId4VpAuthorizationRequest`, SD-JWT payload ni mdoc document.

WP-245 conserva la responsabilidad protocolaria de issuance.
WP-247 conserva la responsabilidad protocolaria de presentation.
WP-248 conserva la responsabilidad de format semantics.

## Seguridad

La interoperabilidad no implica validez criptográfica. Los adapters deberán respetar profile version, supported algorithms, claim constraints, issuer trust, status y holder/device binding definidos por las capas de formato.

## Neutralidad

Foundation no conoce HTTP, JOSE/COSE implementation, persistence, crypto provider ni wallet vendor concreto.

## Criterios de aceptación

Profiles y assessment tipados, contracts separados, ausencia de duplicación protocolaria, neutralidad de infraestructura, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
