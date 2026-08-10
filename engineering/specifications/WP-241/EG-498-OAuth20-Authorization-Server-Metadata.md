---
id: EG-498
title: OAuth 2.0 Authorization Server Metadata
summary: Define el modelo completo, construcción, validación y serialización abstracta de metadata OAuth del Authorization Server.
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
  - authorization-server
depends_on:
  - EG-497
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-498 — OAuth 2.0 Authorization Server Metadata

## Objetivo

Completar el modelo de metadata OAuth del Authorization Server y exponer fronteras tipadas para construcción, validación y serialización.

## Metadata

`OAuthAuthorizationServerMetadata` representa:

- issuer;
- authorization endpoint;
- token endpoint;
- grant types;
- response types;
- scopes;
- token endpoint authentication methods;
- PKCE code challenge methods;
- JWKS URI;
- registration endpoint;
- revocation endpoint;
- introspection endpoint;
- PAR endpoint.

## Builder

`OAuthAuthorizationServerMetadataBuilder` permite construir metadata de forma progresiva sin mutar instancias previas.

## Contratos

- `OAuthAuthorizationServerMetadataProviderInterface`;
- `OAuthAuthorizationServerMetadataValidatorInterface`;
- `OAuthAuthorizationServerMetadataSerializerInterface`.

## Integración con capacidades anteriores

La metadata puede describir capacidades ya incorporadas en:

- WP-239 — OAuth 2.1 Authorization Server;
- WP-240 — PAR y seguridad OAuth avanzada.

La metadata no implementa dichas capacidades; únicamente las publica de forma tipada.

## Neutralidad

Foundation no conoce:

- controlador HTTP;
- JSON encoder concreto;
- cache;
- storage;
- reverse proxy;
- servidor web.

## Criterios de aceptación

- metadata completa y tipada;
- builder persistente;
- serializer abstracto;
- validator abstracto;
- soporte de PKCE/PAR/introspection/revocation;
- infrastructure-neutral;
- PHPUnit focalizado limpio;
- PHPStan limpio;
- Builder sin diagnósticos.
