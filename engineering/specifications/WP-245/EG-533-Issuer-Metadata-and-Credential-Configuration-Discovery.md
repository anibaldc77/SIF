---
id: EG-533
title: Issuer Metadata and Credential Configuration Discovery
summary: Define metadata de Credential Issuer, configuraciones publicadas y fronteras de discovery y validación sin acoplar Foundation a well-known URLs, HTTP, caché o almacenamiento concretos.
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
  - metadata
  - discovery
depends_on:
  - EG-532
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-533 — Issuer Metadata and Credential Configuration Discovery

## Objetivo

Modelar metadata y credential configurations de OpenID4VCI manteniendo discovery y transporte fuera de Foundation.

## Credential Configuration

`CredentialConfiguration` expresa:

- configuration id;
- format;
- cryptographic binding methods;
- proof types;
- metadata opcional.

## Credential Issuer Metadata

`CredentialIssuerMetadata` expresa:

- Credential Issuer identifier;
- Credential Endpoint;
- credential configurations;
- Batch Credential Endpoint opcional;
- Deferred Credential Endpoint opcional;
- Notification Endpoint opcional.

## Assessment

`CredentialIssuerMetadataAssessment` separa:

- valid;
- violations;
- warnings.

## Contratos

- `CredentialIssuerMetadataProviderInterface`;
- `CredentialIssuerMetadataResolverInterface`;
- `CredentialIssuerMetadataValidatorInterface`;
- `CredentialConfigurationResolverInterface`.

## Seguridad

Implementaciones productivas deberán:

- validar issuer identifier;
- verificar consistencia entre metadata y issuer esperado;
- validar endpoints antes de utilizarlos;
- impedir metadata substitution;
- aplicar caché con freshness explícita;
- rechazar configuraciones desconocidas;
- no tratar metadata recuperada como confiable sin validación.

## Neutralidad

Foundation no conoce `.well-known`, HTTP client, DNS, Redis, base de datos, cache engine o formato concreto de metadata.

## Criterios de aceptación

Metadata/configuration/assessment tipados, provider/resolver/validator contracts, discovery neutral, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
