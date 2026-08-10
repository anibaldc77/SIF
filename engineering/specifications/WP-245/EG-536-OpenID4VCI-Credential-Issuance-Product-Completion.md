---
id: EG-536
title: OpenID4VCI Credential Issuance Product Completion
summary: Consolida ofertas, grants, proof-of-possession, batch/deferred issuance, metadata discovery, transaction binding, notifications y readiness como superficie coherente de producto.
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
  - product-completion
depends_on:
  - EG-535
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-536 — OpenID4VCI Credential Issuance Product Completion

## Objetivo

Cerrar WP-245 consolidando I1-I7 como una superficie coherente de OpenID4VCI Credential Issuance.

## Capacidades consolidadas

`CredentialIssuanceProductCapabilities` representa credential offers, authorization code y pre-authorized code grants, proof of possession, batch/deferred issuance, issuer metadata discovery, transaction binding, notifications y operational readiness.

## Product Profile

`CredentialIssuanceProductProfile` expresa nombre, capabilities y requisitos de proof-of-possession, replay protection, transaction binding y operational readiness.

## Readiness

`CredentialIssuanceProductReadinessReport` representa readiness global, blocking issues y warnings. `CredentialIssuanceProductReadinessEvaluatorInterface` define la frontera de evaluación.

## Cobertura acumulada WP-245

1. I1 — architecture, offers y issuance contracts;
2. I2 — authorization code y pre-authorized code grants;
3. I3 — proof-of-possession, c_nonce y replay protection;
4. I4 — credential endpoint, batch y deferred issuance;
5. I5 — issuer metadata y credential configuration discovery;
6. I6 — authorization details y transaction binding;
7. I7 — status lifecycle, notifications y operational readiness;
8. I8 — Product Completion.

## Neutralidad

Foundation no prescribe wallet, HTTP framework, queue broker, storage, Redis, JWT/COSE library, issuer externo o formato concreto de credencial.

## Criterios de aceptación

WP-245 se considera completo cuando PHPUnit, PHPStan, Composer y SIF Builder finalizan sin errores ni diagnósticos y `git diff --check` queda limpio.
