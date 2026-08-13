---
id: EG-578
title: Entity Configuration and Subordinate Statement Validation
summary: Define semantic and temporal validation rules for OpenID Federation Entity Configurations and Subordinate Statements while keeping cryptographic verification and transport behind specialized contracts.
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
  - entity-statements
  - validation
depends_on:
  - EG-577
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-578 — Entity Configuration and Subordinate Statement Validation

## Objetivo

Definir validaciones semánticas y temporales para Entity Configurations y Subordinate Statements de OpenID Federation.

## Validation Context

`OpenIdFederationStatementValidationContext` expresa:

- instante de evaluación;
- Entity Identifier esperado opcional.

## Entity Configuration

La validación exige:

- self-issued semantics;
- statement vigente;
- `iat` no futuro;
- ausencia de `metadata_policy`;
- coincidencia con el Entity Identifier esperado cuando se configure.

## Subordinate Statement

La validación exige:

- issuer y subject distintos;
- statement vigente;
- `iat` no futuro;
- ausencia de `authority_hints`;
- coincidencia con el Entity Identifier esperado cuando se configure.

## Separación de responsabilidades

La validación semántica no reemplaza `OpenIdFederationEntityStatementVerifierInterface`.

Firma, JWS/JWT processing y Federation Entity Key resolution permanecen detrás de adapters especializados.

## Neutralidad

Foundation no implementa HTTP, JWS/JWT library, key retrieval, persistence ni crypto provider concreto.

## Compatibilidad

I2 agrega policies de validation sin modificar contratos de I1 ni la infraestructura genérica de WP-250.
