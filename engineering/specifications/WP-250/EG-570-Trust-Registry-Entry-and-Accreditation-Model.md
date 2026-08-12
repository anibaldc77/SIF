---
id: EG-570
title: Trust Registry Entry and Accreditation Model
summary: Define registry membership and accreditation models for credential trust ecosystems without coupling Foundation to a concrete registry, federation, PKI, transport or persistence implementation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-250
tags:
  - security
  - verifiable-credentials
  - trust
  - trust-registry
  - accreditation
depends_on:
  - EG-569
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-570 — Trust Registry Entry and Accreditation Model

## Objetivo

Modelar membership de trust registries y accreditation de entidades sin introducir dependencias de infraestructura.

## Registry Membership

`CredentialTrustRegistryMembershipStatus` distingue active, suspended, revoked y expired.

`CredentialTrustRegistryEntry` representa registry id, entity reference, membership status, validity interval, accreditation ids y metadata adicional.

## Accreditation

`CredentialAccreditationScope` expresa scope id, credential types, jurisdictions y constraints.

`CredentialAccreditation` vincula accreditation id, subject, accreditation authority, scope y validity interval.

## Contratos

- `CredentialTrustRegistryResolverInterface`;
- `CredentialAccreditationResolverInterface`;
- `CredentialAccreditationPolicyInterface`;
- `CredentialTrustRegistryMembershipPolicyInterface`.

## Compatibilidad

I2 no modifica los contracts de trust definidos en I1 ni los contratos históricos de credential trust/issuer metadata.

## Neutralidad

Foundation no conoce implementación concreta de registry, OpenID Federation, X.509, HTTP, Redis, PDO o crypto provider.
