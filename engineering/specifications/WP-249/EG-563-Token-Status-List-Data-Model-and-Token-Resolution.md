---
id: EG-563
title: Token Status List Data Model and Token Resolution
summary: Define a version-aware Token Status List data model, decoding boundary and deterministic status-value resolution without coupling Foundation to JOSE, COSE, compression or transport implementations.
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
  - token-status-list
  - status-resolution
depends_on:
  - EG-562
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-563 — Token Status List Data Model and Token Resolution

## Objetivo

Modelar Token Status List como mecanismo de status separado de su transporte, protección criptográfica y compresión concreta.

## Reference

`TokenStatusListReference` representa URI e index del referenced token.

## Status List

`TokenStatusList` representa:

- encoded list;
- bits per status;
- TTL opcional;
- aggregation URI opcional.

## Decoded Data

`TokenStatusListDecodedData` representa los bytes ya decodificados y el ancho de cada status.

## Status Value

`TokenStatusListValue` representa el valor resuelto y su bit width.

`TokenStatusListValueResolver` realiza resolución determinística sobre los bytes ya decodificados y rechaza índices fuera de rango.

## Contratos

- `TokenStatusListDecoderInterface`;
- `TokenStatusListAuthenticityVerifierInterface`;
- `TokenStatusListValuePolicyInterface`.

## Seguridad

La decodificación no implica autenticidad. La implementación productiva deberá verificar la protección del Status List Token, su subject/URI binding, temporal claims y freshness antes de confiar en el status value.

## Versionado

El mecanismo `TokenStatusList` continúa utilizando `CredentialStatusProfile.profileVersion`; Foundation no fija una revisión IETF concreta en contracts públicos.

## Neutralidad

Foundation no implementa JOSE, COSE, JWT/CWT parsing, zlib/DEFLATE, HTTP, cache o persistence concreta.

## Criterios de aceptación

Reference/list/decoded data/value tipados, resolución multi-bit determinística, contracts separados, neutralidad de infraestructura, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
