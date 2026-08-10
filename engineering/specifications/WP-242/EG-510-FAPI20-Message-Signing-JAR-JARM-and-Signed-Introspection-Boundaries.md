---
id: EG-510
title: FAPI 2.0 Message Signing JAR JARM and Signed Introspection Boundaries
summary: Define requisitos y fronteras FAPI para JAR, JARM y respuestas de introspección firmadas sin acoplar Foundation a una implementación JWT/JWS concreta.
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
  - jar
  - jarm
  - introspection
depends_on:
  - EG-509
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-510 — FAPI 2.0 Message Signing: JAR, JARM and Signed Introspection Boundaries

## Objetivo

Modelar los requisitos de Message Signing de FAPI 2.0 sin reimplementar JAR ni acoplar Foundation a una librería JWT/JWS concreta.

## Requirements

`FapiMessageSigningRequirements` expresa:

- JAR requerido;
- JARM requerido;
- signed introspection requerido;
- algoritmos permitidos para cada frontera.

## Modelos

`FapiSignedAuthorizationResponse` representa una authorization response validada con issuer, audience, code y state.

`FapiSignedIntrospectionResponse` representa una introspection response firmada, incluyendo active, issuer, audience y claims.

## Assessment

`FapiMessageSigningAssessment` expresa conformidad y violaciones.

## Contratos

- `FapiMessageSigningPolicyInterface`;
- `FapiMessageSigningRequirementsProviderInterface`;
- `FapiJarmVerifierInterface`;
- `FapiSignedIntrospectionVerifierInterface`.

## Reutilización

JAR reutiliza la frontera de request objects incorporada en WP-240.

I6 agrega las fronteras de JARM y signed introspection necesarias para el perfil de Message Signing.

## Seguridad

Implementaciones productivas deberán:

- restringir algoritmos permitidos;
- validar issuer y audience;
- validar state;
- verificar firma antes de consumir claims;
- rechazar algoritmos no permitidos;
- no aceptar respuestas unsigned cuando el perfil las exige firmadas.

## Neutralidad

Foundation no conoce OpenSSL directo, Firebase JWT, Lcobucci JWT, HSM, HTTP framework ni storage.

## Criterios de aceptación

Requirements tipados, modelos firmados tipados, assessment, verifier contracts, crypto-neutrality, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
