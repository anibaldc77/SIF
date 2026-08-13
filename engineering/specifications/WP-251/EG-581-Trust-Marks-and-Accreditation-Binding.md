---
id: EG-581
title: Trust Marks and Accreditation Binding
summary: Define OpenID Federation Trust Mark models, semantic validation and binding to the generic credential accreditation infrastructure completed in WP-250.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-12
updated: 2026-08-12
work_package: WP-251
tags:
  - security
  - federation
  - openid-federation
  - trust-marks
  - accreditation
depends_on:
  - EG-580
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-581 — Trust Marks and Accreditation Binding

## Objetivo

Definir Trust Marks protocol-specific de OpenID Federation y su bridge hacia la infraestructura genérica de accreditation de WP-250.

## Trust Mark

`OpenIdFederationTrustMark` representa:

- Trust Mark identifier;
- issuer entity id;
- subject entity id;
- issued timestamp;
- expiration opcional;
- claims adicionales.

La representación es posterior a parsing/cryptographic verification realizado por adapters especializados.

## Validation

`OpenIdFederationTrustMarkValidationContext` expresa instante, subject esperado y Trust Mark identifier esperado.

`DefaultOpenIdFederationTrustMarkValidator` valida:

- `iat` no futuro;
- vigencia;
- subject esperado;
- identifier esperado.

## Accreditation Binding

`OpenIdFederationAccreditationBinding` vincula un Trust Mark con `CredentialAccreditation`.

El binding exige:

- Trust Mark subject = accreditation subject;
- Trust Mark issuer = accreditation authority.

No se crea un segundo modelo de accreditation.

## Contratos

- `OpenIdFederationTrustMarkResolverInterface`;
- `OpenIdFederationTrustMarkValidationPolicyInterface`;
- `OpenIdFederationAccreditationBindingPolicyInterface`.

## Seguridad

La existencia de un Trust Mark no implica confianza automática.

Firma, issuer trust, validity, metadata policy, accreditation scope y trust-chain enforcement son verificaciones separadas.

## Neutralidad

Foundation no implementa JWT/JWS library, HTTP, persistence, Redis, PDO ni crypto provider concreto.

## Compatibilidad

I5 agrega Trust Marks y accreditation binding sin modificar I1-I4 ni WP-250.
