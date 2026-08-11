---
id: EG-561
title: Credential Status Revocation and Suspension Architecture
summary: Define version-aware, privacy-conscious status boundaries for credential revocation and suspension supporting Bitstring Status List and Token Status List mechanisms.
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
depends_on:
  - EG-560
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-561 — Credential Status, Revocation and Suspension Architecture

## Objetivo

Crear una infraestructura neutral para status de credenciales, separando referencias, resolución, freshness y policy.

## Status Purposes

`CredentialStatusPurpose` reconoce revocation y suspension.

## Status Mechanisms

`CredentialStatusMechanism` reconoce:

- Bitstring Status List;
- Token Status List.

El mecanismo se mantiene separado del profile version para tolerar evolución normativa.

## Status Profile

`CredentialStatusProfile` representa mecanismo, versión, purposes soportados y opciones.

## Status Reference

`CredentialStatusReference` representa credential id, status list URI, index y purpose.

## Assessment

`CredentialStatusAssessment` separa:

- validez;
- revoked;
- suspended;
- evaluation timestamp;
- source update timestamp;
- violations;
- warnings.

## Contratos

- `CredentialStatusResolverInterface`;
- `CredentialStatusProfilePolicyInterface`;
- `CredentialStatusFreshnessPolicyInterface`.

## Seguridad y privacidad

La resolución deberá evitar tracking innecesario, validar freshness, controlar status-list substitution y no asumir que una respuesta obtenida implica autenticidad.

## Neutralidad

Foundation no conoce HTTP, JWT, VC Data Integrity, compression library, persistence o cache concreto.

## Roadmap WP-249

1. I1 — architecture y version-aware status contracts;
2. I2 — Bitstring Status List data model y bit resolution;
3. I3 — Token Status List data model y token resolution;
4. I4 — issuer publication, allocation y lifecycle;
5. I5 — verifier resolution, caching y freshness;
6. I6 — SD-JWT VC / W3C VC / OpenID interoperability;
7. I7 — privacy, resilience y operational readiness;
8. I8 — Product Completion.

## Criterios de aceptación

Contratos tipados, status purpose/mecanismo/version separados, neutralidad de infraestructura, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
