---
id: EG-580
title: Metadata Policy Resolution and Standard Operators
summary: Define OpenID Federation 1.0 metadata policy models, the seven standard operators, application results and policy resolution boundaries.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-12
updated: 2026-08-12
work_package: WP-251
tags:
  - security
  - federation
  - openid-federation
  - metadata-policy
  - trust-chain
depends_on:
  - EG-579
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-580 — Metadata Policy Resolution and Standard Operators

## Objetivo

Definir la representación, resolución y aplicación de Metadata Policies de OpenID Federation 1.0.

## Standard Operators

SIF reconoce los siete operadores estándar:

- `value`;
- `add`;
- `default`;
- `one_of`;
- `subset_of`;
- `superset_of`;
- `essential`.

## Parameter Policy

`OpenIdFederationMetadataParameterPolicy` conserva operadores y valores de un parámetro individual.

Los operadores estándar desconocidos son rechazados por el modelo estándar. Operadores adicionales se incorporarán únicamente mediante extensiones explícitas.

## Metadata Policy

`OpenIdFederationMetadataPolicy` organiza policies por Entity Type y Metadata Parameter, y conserva `metadata_policy_crit`.

## Resolution

`OpenIdFederationMetadataPolicyResolverInterface` define el boundary para combinar las policies de los Subordinate Statements de una Trust Chain.

`OpenIdFederationResolvedMetadataPolicy` conserva policy resultante y entidades fuente.

## Application

`OpenIdFederationMetadataPolicyApplicatorInterface` aplica una policy resuelta a metadata de un Entity Type.

`DefaultOpenIdFederationMetadataPolicyApplicator` implementa el comportamiento base de los siete operadores estándar.

## Seguridad

Un error de policy debe impedir considerar válida la resolución de trust correspondiente.

La capa de metadata policy no reemplaza la validación de Entity Statements ni el trust-chain enforcement de WP-250.

## Neutralidad

Foundation no implementa HTTP, JWT/JWS, persistence ni crypto concreto.

## Compatibilidad

I4 agrega metadata-policy models y contracts sin modificar I1-I3 ni WP-250.
