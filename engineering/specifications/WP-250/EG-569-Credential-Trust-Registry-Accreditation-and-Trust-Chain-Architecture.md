---
id: EG-569
title: Credential Trust Registry Accreditation and Trust Chain Architecture
summary: Define version-aware trust models, ecosystem entity roles and trust-chain evaluation boundaries without replacing the credential trust and issuer metadata contracts already provided by SIF.
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
  - federation
depends_on:
  - EG-568
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-569 — Credential Trust Registry, Accreditation and Trust Chain Architecture

## Objetivo

Extender las capacidades de trust existentes de SIF con una arquitectura para trust registries, accreditation y cadenas de confianza.

WP-250 no reemplaza:

- `CredentialTrustPolicyInterface`;
- `CredentialIssuerTrustResolverInterface`;
- `CredentialIssuerMetadataResolverInterface`;
- `CredentialIssuerMetadataValidatorInterface`.

## Trust Models

`CredentialTrustModel` distingue:

- direct;
- registry;
- federation;
- PKI.

El modelo de confianza y su profile version permanecen separados para permitir evolución de estándares y ecosystems.

## Entity Roles

`CredentialTrustEntityRole` distingue issuer, verifier, wallet, trust anchor y accreditation authority.

## Trust Profile

`CredentialTrustProfile` expresa:

- nombre;
- profile version;
- modelos permitidos;
- roles requeridos;
- opciones adicionales.

## Trust Entity Reference

`CredentialTrustEntityReference` representa el identificador de una entidad, su rol y un trust-framework id opcional.

## Trust Chain

`CredentialTrustChainContext` expresa:

- instante de evaluación;
- trust anchors aceptados;
- profundidad máxima.

`CredentialTrustChainAssessment` representa:

- trusted;
- path entity ids;
- violations;
- warnings.

## Contratos

- `CredentialTrustChainEvaluatorInterface`;
- `CredentialTrustProfilePolicyInterface`;
- `CredentialTrustEntityRolePolicyInterface`.

## Neutralidad

Foundation no conoce implementación concreta de registry, OpenID Federation, X.509 path building, HTTP, Redis, PDO o crypto provider.

## Roadmap WP-250

1. I1 — trust architecture y version-aware contracts;
2. I2 — trust registry entry y accreditation model;
3. I3 — trust anchors y key-material lifecycle boundaries;
4. I4 — registry membership, delegation y accreditation chains;
5. I5 — metadata/trust resolution, caching y freshness;
6. I6 — trust decision, failure policy y resilience;
7. I7 — high-assurance trust enforcement y operational readiness;
8. I8 — Product Completion.

## Criterios de aceptación

Modelos y roles explícitos, profiles versionados, contracts especializados, compatibilidad con trust contracts existentes, neutralidad de infraestructura y validaciones verdes.
