---
id: EG-568
title: Credential Status Product Completion
summary: Define the final product surface and release-readiness gate for credential status, revocation, suspension and freshness processing in SIF.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-249
tags:
  - security
  - verifiable-credentials
  - credential-status
  - revocation
  - suspension
  - product-completion
depends_on:
  - EG-567
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-568 — Credential Status Product Completion

## Objetivo

Cerrar WP-249 consolidando I1-I7 como una superficie coherente para resolución, publicación y enforcement de credential status.

## Capacidades consolidadas

La superficie final cubre:

- status resolution;
- revocation y suspension;
- Bitstring Status List;
- Token Status List;
- issuer publication lifecycle;
- verifier caching;
- freshness y refresh;
- resolution failure policy;
- high-assurance enforcement;
- operational readiness.

## Product Profile

`CredentialStatusProductProfile` exige:

- status validation;
- fresh status evidence;
- fail-closed para high-assurance;
- operational readiness.

## Product Readiness

`CredentialStatusProductReadinessReport` representa ready, blocking issues y warnings.

`CredentialStatusProductReadinessEvaluatorInterface` define el release gate.

## Compatibilidad

Product Completion agrega una capa de consolidación sin modificar los contratos especializados de I1-I7.

## Neutralidad

Foundation no prescribe PDO, Redis, HTTP client, frameworks externos ni mecanismo de almacenamiento concreto.