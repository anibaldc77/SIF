---
id: EG-571
title: Trust Anchors and Key Material Lifecycle Boundaries
summary: Define trust-anchor and key-material lifecycle boundaries for credential trust ecosystems without coupling Foundation to PKI, JWKS, HSM, KMS, transport or storage implementations.
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
  - trust-anchor
  - key-lifecycle
depends_on:
  - EG-570
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-571 — Trust Anchors and Key Material Lifecycle Boundaries

## Objetivo
Modelar trust anchors y lifecycle de key material sin introducir una implementación concreta de PKI, JWKS, HSM, KMS o storage.

## Trust Anchor
`CredentialTrustAnchorStatus` distingue active, suspended, retired y revoked. `CredentialTrustAnchor` representa anchor id, entity reference, status, validity interval, key-material ids y metadata.

## Key Material
`CredentialTrustKeyMaterialStatus` distingue active, rotating, retired y revoked. `CredentialTrustKeyMaterial` representa key id, owner entity id, status, validity interval, usages y metadata.

## Lifecycle
`CredentialTrustKeyLifecycleTransition` expresa source state, target state, effective timestamp y reason.

## Contratos
- `CredentialTrustAnchorResolverInterface`;
- `CredentialTrustAnchorPolicyInterface`;
- `CredentialTrustKeyMaterialResolverInterface`;
- `CredentialTrustKeyLifecyclePolicyInterface`.

## Neutralidad
Foundation no conoce X.509 path building, JWKS, HSM, KMS, HTTP, Redis, PDO ni crypto provider concreto.
