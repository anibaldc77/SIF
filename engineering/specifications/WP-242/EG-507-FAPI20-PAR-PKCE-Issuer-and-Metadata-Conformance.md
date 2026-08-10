---
id: EG-507
title: FAPI 2.0 PAR, PKCE, Issuer and Metadata Conformance
summary: Define requisitos y assessment FAPI para PAR, PKCE S256, issuer identification y consumo seguro de Authorization Server Metadata.
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
  - par
  - pkce
  - metadata
depends_on:
  - EG-506
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-507 — FAPI 2.0 PAR, PKCE, Issuer and Metadata Conformance

## Objetivo

Convertir los requisitos normativos de authorization flow y discovery de FAPI 2.0 en policies y assessments verificables sobre las capacidades ya implementadas por SIF.

## Authorization Flow

`FapiAuthorizationRequestSecurityRequirements` expresa:

- response type `code`;
- uso obligatorio de PAR;
- PAR autenticado por cliente;
- PKCE S256;
- redirect URI dentro de PAR;
- validación del `iss` de la authorization response;
- lifetime máximo de PAR inferior a 600 segundos.

## Metadata

`FapiMetadataConformanceRequirements` expresa:

- issuer obtenido desde fuente autoritativa;
- exact issuer matching;
- endpoints consumidos desde metadata;
- HTTPS requerido.

## Assessments

Se incorporan:

- `FapiAuthorizationFlowAssessment`;
- `FapiMetadataConformanceAssessment`.

## Contratos

- `FapiAuthorizationFlowPolicyInterface`;
- `FapiMetadataConformancePolicyInterface`;
- requirements providers para ambos perfiles.

## Reutilización

I3 no implementa PAR, PKCE, issuer validation o metadata discovery.

Consume las capacidades ya disponibles en WP-240 y WP-241 y agrega reglas de conformidad FAPI.

## Seguridad

Las implementaciones productivas deberán rechazar:

- authorization requests fuera de PAR;
- PAR sin client authentication;
- PKCE distinto de S256;
- redirect URI ausente en PAR;
- issuer mismatch;
- endpoints no obtenidos desde metadata confiable;
- metadata obtenida desde una fuente no autoritativa;
- PAR con expiración de 600 segundos o superior.

## Neutralidad

Foundation no conoce networking, HTTP client, cache concreta, storage ni framework web.

## Criterios de aceptación

Requisitos tipados, assessments explícitos, contratos neutrales, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
