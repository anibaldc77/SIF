---
id: EG-572
title: Trust Chain Resolution and Validation
summary: Define deterministic credential trust-chain resolution, link validation, cycle detection and depth boundaries without coupling Foundation to OpenID Federation, PKI, HTTP, storage or crypto implementations.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-12
updated: 2026-08-12
work_package: WP-250
tags:
  - security
  - verifiable-credentials
  - trust
  - trust-chain
  - validation
depends_on:
  - EG-571
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-572 — Trust Chain Resolution and Validation

## Objetivo

Definir una capa explícita para construcción y validación de cadenas de confianza sobre las primitivas desarrolladas en I1-I3.

## Trust Chain Link

`CredentialTrustChainLink` representa entity, parent entity id, accreditation ids y key-material ids.

## Trust Chain

`CredentialTrustChain` mantiene una secuencia ordenada leaf-to-root y expone depth, leaf y root.

## Cycle Detection

`CredentialTrustChainCycleDetector` detecta reutilización de entity ids dentro de una misma cadena.

## Validation Result

`CredentialTrustChainValidationResult` expresa valid, violations y warnings.

## Contratos

- `CredentialTrustChainResolverInterface`;
- `CredentialTrustChainValidationPolicyInterface`;
- `CredentialTrustChainLinkPolicyInterface`.

## Seguridad

Las implementaciones productivas deberán validar como mínimo chain depth, ausencia de ciclos, parent relationship, temporal validity, registry membership, accreditation validity, allowed trust anchor y acceptable key material.

## Neutralidad

Foundation no implementa OpenID Federation, X.509 path building, JWKS retrieval, HTTP, Redis, PDO ni crypto concreto.

## Compatibilidad

I4 agrega tipos y contratos sin modificar las superficies públicas de I1-I3.
