---
id: EG-554
title: SD-JWT VC Data Model and Selective Disclosure Boundaries
summary: Define SD-JWT VC disclosures, digest references, credential payload and key binding boundaries without coupling Foundation to JWT, hashing or cryptographic libraries.
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
  - selective-disclosure
  - key-binding
depends_on:
  - EG-553
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-554 — SD-JWT VC Data Model and Selective Disclosure Boundaries

## Objetivo

Modelar selective disclosure y key binding de SD-JWT VC sin incorporar parsing JWT/JWS, hashing o criptografía concreta en Foundation.

## Disclosure

`SdJwtVcDisclosure` representa:

- salt;
- claim name;
- claim value.

## Disclosure Reference

`SdJwtVcDisclosureReference` representa digest y algoritmo de hash declarado.

## Credential Payload

`SdJwtVcCredentialPayload` representa:

- issuer;
- verifiable credential type (`vct`);
- claims;
- disclosure references;
- subject opcional.

## Selective Disclosure Set

`SdJwtVcSelectiveDisclosureSet` distingue disclosures reveladas y digests que permanecen sin revelar.

## Key Binding

`SdJwtVcKeyBindingContext` mantiene audience, nonce y holder key id opcional.

## Contratos

- `SdJwtVcDisclosureDigestVerifierInterface`;
- `SdJwtVcSelectiveDisclosurePolicyInterface`;
- `SdJwtVcKeyBindingPolicyInterface`.

## Seguridad

La implementación concreta deberá verificar que cada disclosure corresponda a un digest autorizado, impedir duplicados/conflictos, validar audience/nonce del key binding y no asumir que disclosure parseada implica disclosure válida.

## Neutralidad

Foundation no conoce JWT/JWS parser, SHA implementation, cryptographic provider, HTTP client o persistence concreta.

## Criterios de aceptación

Disclosure/reference/payload/set/key binding tipados, contratos separados, neutralidad de implementación, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
