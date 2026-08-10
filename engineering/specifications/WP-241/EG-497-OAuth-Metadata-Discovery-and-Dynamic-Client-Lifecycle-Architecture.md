---
id: EG-497
title: Arquitectura de OAuth Metadata, Discovery y Dynamic Client Lifecycle
summary: Define la arquitectura contract-first para metadata del Authorization Server y Protected Resource, discovery, registro dinámico y lifecycle de clientes OAuth sobre WP-239 y WP-240.
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
  - dynamic-client-registration
depends_on:
  - EG-496
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-497 — OAuth Metadata, Discovery and Dynamic Client Lifecycle Architecture

## Objetivo

Completar las capacidades de metadata y lifecycle de clientes que quedaron deliberadamente como fronteras en WP-240.

WP-241 se construye sobre WP-239 y WP-240 sin duplicar Authorization Server, OIDC Provider Metadata ni Resource Server enforcement.

## Base normativa

El alcance está alineado con:

- OAuth 2.0 Authorization Server Metadata;
- OAuth 2.0 Dynamic Client Registration;
- OAuth 2.0 Dynamic Client Registration Management;
- OAuth 2.0 Protected Resource Metadata;
- OAuth Authorization Server Issuer Identification.

## Modelos iniciales

### Authorization Server Metadata

`OAuthAuthorizationServerMetadata` representa:

- issuer;
- authorization endpoint;
- token endpoint;
- grant types;
- response types;
- scopes;
- JWKS URI opcional;
- registration endpoint opcional.

### Protected Resource Metadata

`OAuthProtectedResourceMetadata` representa:

- resource identifier;
- authorization servers;
- scopes soportados.

### Client Registration Metadata

`OAuthClientRegistrationMetadata` representa el input declarativo de un registro y permanece separado del `OAuthClient` persistido.

## Contratos

- `OAuthAuthorizationServerMetadataProviderInterface`;
- `OAuthProtectedResourceMetadataProviderInterface`;
- `OAuthDynamicClientRegistrationServiceInterface`;
- `OAuthClientRegistrationMetadataValidatorInterface`;
- `OAuthMetadataResolverInterface`.

WP-240 ya aporta `OAuthClientLifecycleManagerInterface`; WP-241 deberá evolucionar alrededor de esa frontera sin duplicarla.

## Separación respecto de OIDC

OIDC Provider Metadata continúa bajo `Foundation/Security/Oidc`.

WP-241 define metadata OAuth genérica y no introduce claims, ID Token o UserInfo propios de OpenID Connect.

## Neutralidad

Foundation no conoce:

- HTTP client concreto;
- controllers;
- DNS;
- cache;
- persistencia;
- Redis;
- proveedor IAM;
- serializador JSON concreto.

## Roadmap I1-I8

1. I1 — metadata/discovery y client lifecycle architecture;
2. I2 — Authorization Server Metadata;
3. I3 — Protected Resource Metadata;
4. I4 — Dynamic Client Registration;
5. I5 — Client Registration Management lifecycle;
6. I6 — client metadata validation, software statements y security policy boundaries;
7. I7 — issuer identification, discovery resolution, caching/freshness boundaries;
8. I8 — product completion, integration tests y adoption guide.

## Criterios de aceptación

- arquitectura separada de OIDC;
- metadata tipada;
- registration metadata separada de OAuthClient;
- contratos neutrales;
- sin duplicación de WP-239/WP-240;
- PHPUnit focalizado limpio;
- PHPStan limpio;
- Builder sin diagnósticos.
