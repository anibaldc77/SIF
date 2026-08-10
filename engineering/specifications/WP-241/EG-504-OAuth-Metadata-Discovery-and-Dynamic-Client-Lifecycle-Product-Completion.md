---
id: EG-504
title: OAuth Metadata, Discovery and Dynamic Client Lifecycle Product Completion
summary: Consolida Authorization Server Metadata, Protected Resource Metadata, Dynamic Client Registration, Registration Management, software statements, issuer validation y discovery como superficie coherente de producto.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-09
updated: 2026-08-09
work_package: WP-241
tags:
  - security
  - oauth
  - metadata
  - discovery
  - client-registration
depends_on:
  - EG-503
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-504 — OAuth Metadata, Discovery and Dynamic Client Lifecycle Product Completion

## Objetivo
Cerrar WP-241 consolidando las capacidades construidas en I1-I7 como una superficie coherente y verificable de producto.

## Capacidades consolidadas
Authorization Server Metadata, Protected Resource Metadata, Dynamic Client Registration, Client Registration Management, Software Statements, Issuer Validation y Discovery Resolution.

## Product Profile
`OAuthMetadataProductProfile` expresa exact issuer matching, HTTPS discovery, fresh metadata y habilitación de dynamic registration.

## Readiness
`OAuthMetadataProductReadinessReport` representa readiness, capacidades faltantes y warnings. `OAuthMetadataProductReadinessEvaluatorInterface` define la frontera de evaluación.

## Compatibilidad acumulada
Se preservan `OAuthMetadataResolverInterface::resolve()`, `OAuthClientRegistrationMetadataValidatorInterface::validate()` y `OAuthDynamicClientRegistrationServiceInterface::register()`.

## Neutralidad
Foundation no prescribe HTTP client, networking, Redis, base de datos, JWT/JWS library, Vault, framework HTTP ni proveedor IAM.

## Criterios de aceptación
PHPUnit, PHPStan, Composer, SIF Builder y `git diff --check` deben quedar limpios.
