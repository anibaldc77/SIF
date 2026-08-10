---
id: EG-505
title: FAPI 2.0 High-Security API Profile Architecture
summary: Define una capa de perfil y conformidad FAPI 2.0 sobre las capacidades OAuth/OIDC existentes de SIF sin duplicar los protocolos subyacentes.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-09
updated: 2026-08-09
work_package: WP-242
tags:
  - security
  - oauth
  - fapi
  - high-security
depends_on:
  - EG-504
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-505 — FAPI 2.0 High-Security API Profile Architecture

## Objetivo

Introducir FAPI 2.0 como una capa de perfil y conformidad sobre las capacidades ya implementadas por SIF.

WP-242 no reimplementa OAuth, OIDC, PAR, PKCE, DPoP, metadata ni sender-constrained tokens.

## Fundamento

FAPI 2.0 define un perfil OAuth de alta seguridad orientado a APIs de alto valor.

La arquitectura de SIF debe expresar los requisitos del perfil mediante capabilities y policies verificables.

## Capacidades

`FapiSecurityCapability` incluye:

- confidential clients;
- PKCE;
- PAR;
- sender-constrained tokens;
- issuer validation;
- metadata discovery;
- JAR;
- JARM;
- signed introspection responses.

## Perfil

`FapiSecurityProfile` representa un conjunto de capacidades requeridas.

La selección exacta pertenece al perfil o ecosistema adoptado y no debe eliminar requisitos obligatorios del estándar aplicable.

## Conformance

`FapiConformanceReport` expresa:

- conformidad;
- capacidades faltantes;
- warnings.

`FapiConformanceEvaluatorInterface` define la frontera de evaluación.

## Policies por rol

Se definen fronteras independientes para:

- client;
- authorization server;
- resource server.

Esto evita mezclar responsabilidades y permite adapters específicos por deployment.

## Reutilización

WP-242 consume capacidades previas de SIF:

- WP-233 Resource Server;
- WP-234 OIDC;
- WP-239 OAuth Authorization Server;
- WP-240 Advanced OAuth Security;
- WP-241 Metadata, Discovery and Client Lifecycle.

## Roadmap I1-I8

1. I1 — FAPI 2.0 architecture y conformance contracts;
2. I2 — client and authorization server security profile;
3. I3 — PAR, PKCE, issuer y metadata conformance;
4. I4 — sender-constrained tokens con DPoP/mTLS policy;
5. I5 — resource server enforcement profile;
6. I6 — Message Signing: JAR, JARM y signed introspection boundaries;
7. I7 — ecosystem profile, conformance/readiness y deployment policy;
8. I8 — product completion, integration tests y adoption guide.

## Neutralidad

Foundation no conoce:

- HTTP framework;
- storage;
- TLS termination concreta;
- JWT/JWS library concreta;
- OpenID conformance tooling concreto;
- proveedor IAM.

## Criterios de aceptación

- FAPI como profile layer;
- reutilización de capacidades existentes;
- policies separadas por rol;
- conformance model tipado;
- infrastructure-neutral;
- PHPUnit focalizado limpio;
- PHPStan limpio;
- Builder sin diagnósticos.
