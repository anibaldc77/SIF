---
id: EG-506
title: FAPI 2.0 Client and Authorization Server Security Profile
summary: Define requisitos y assessment tipados para clientes y Authorization Servers bajo el perfil FAPI 2.0, preservando compatibilidad con las fronteras de I1.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-242
tags:
  - security
  - oauth
  - fapi
  - client
  - authorization-server
depends_on:
  - EG-505
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-506 — FAPI 2.0 Client and Authorization Server Security Profile

## Objetivo

Convertir la arquitectura FAPI de I1 en requisitos concretos y verificables para Client y Authorization Server.

## Client Requirements

`FapiClientSecurityRequirements` expresa confidential client, PKCE, PAR, sender-constrained tokens y métodos de autenticación permitidos.

## Authorization Server Requirements

`FapiAuthorizationServerSecurityRequirements` expresa PAR endpoint, issuer metadata, sender-constrained tokens y métodos PKCE soportados.

## Assessments

Se incorporan `FapiClientSecurityAssessment` y `FapiAuthorizationServerSecurityAssessment`.

## Compatibilidad incremental

Se conservan `FapiClientPolicyInterface::validate()` y `FapiAuthorizationServerPolicyInterface::validateConfiguration()`, agregando `assess()`.

## Neutralidad

Foundation no conoce TLS termination, OpenSSL directo, HTTP framework, storage, reverse proxy ni proveedor IAM.

## Criterios de aceptación

Requisitos tipados, assessment explícito, compatibilidad I1 preservada, providers de requirements, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
