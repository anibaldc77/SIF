---
id: EG-560
title: SD-JWT VC and ISO mdoc Product Completion
summary: Define the final product surface and release-readiness gate for SD-JWT VC and ISO mdoc high-assurance credential formats in SIF.
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
  - product-completion
depends_on:
  - EG-559
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-560 — SD-JWT VC and ISO mdoc Product Completion

## Objetivo

Cerrar WP-248 consolidando I1-I7 como una superficie coherente de formatos de credenciales de alta garantía.

## Capacidades consolidadas

`HighAssuranceCredentialProductCapabilities` representa:

- SD-JWT VC;
- ISO mdoc;
- selective disclosure;
- issuer trust;
- status validation;
- holder binding;
- MSO validation;
- device authentication;
- OpenID4VCI interoperability;
- OpenID4VP interoperability;
- privacy policy;
- operational readiness.

## Product Profile

`HighAssuranceCredentialProductProfile` expresa requisitos mínimos de seguridad y readiness sin sustituir los contracts especializados.

## Product Readiness

`HighAssuranceCredentialProductReadinessReport` representa ready, blocking issues y warnings.

`HighAssuranceCredentialProductReadinessEvaluatorInterface` define el release gate.

## Cobertura acumulada WP-248

1. I1 — architecture y version-aware format contracts;
2. I2 — SD-JWT VC data model y selective disclosure boundaries;
3. I3 — SD-JWT VC issuer trust, status y key binding;
4. I4 — ISO mdoc namespace/data elements y device response;
5. I5 — ISO mdoc issuer authentication, MSO y device authentication;
6. I6 — OpenID4VCI/OpenID4VP interoperability adapters;
7. I7 — high-assurance profile, privacy y operational readiness;
8. I8 — Product Completion.

## Compatibilidad

Los profiles versionados evitan congelar SIF contra una única revisión externa. El producto conserva separación entre semantics de formato, protocolos de issuance/presentation y adapters criptográficos.

## Neutralidad

Foundation no prescribe JOSE, COSE, CBOR, X.509, HSM, HTTP, storage, wallet vendor ni policy engine concreto.

## Criterios de cierre

WP-248 se considera técnicamente completo cuando PHPUnit, PHPStan, Composer, SIF Builder y `git diff --check` finalizan correctamente; el cierre Git se realiza mediante commit único, tags I1-I8, tag complete y push.
