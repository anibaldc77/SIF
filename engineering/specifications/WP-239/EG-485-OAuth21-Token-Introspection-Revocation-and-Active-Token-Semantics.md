---
id: EG-485
title: Introspección, revocación y semántica de token activo en OAuth 2.1
summary: Define introspection, revocation requests y active token semantics sin acoplar Foundation a HTTP, storage o formato JWT.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-239
tags:
  - security
  - oauth
  - introspection
  - revocation
  - token-status
depends_on:
  - EG-484
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-485 — OAuth 2.1 Token Introspection, Revocation and Active Token Semantics

## Objetivo

Incorporar semántica explícita de estado, introspection y revocation manteniendo Foundation neutral respecto de endpoints HTTP y formato del token.

## Active token

`OAuthTokenStatus` considera un token activo únicamente cuando:

- no está revocado;
- no alcanzó su expiración.

## Introspection

`OAuthTokenIntrospection` representa:

- active;
- client id;
- scopes;
- expiresAt;
- subject opcional.

No presupone JWT.

## Revocation

`OAuthTokenRevocationRequest` contiene:

- token;
- token type hint opcional.

## Contracts

- `OAuthTokenIntrospectorInterface`;
- `OAuthTokenRevokerInterface`;
- `OAuthTokenStatusProviderInterface`.

## Neutralidad

I5 no conoce:

- endpoint HTTP concreto;
- status codes;
- Authorization header;
- JWT/JWK/JWS;
- base de datos;
- cache.

## Seguridad

Adapters productivos deben resolver:

- autorización del cliente que introspecta;
- autenticación del cliente;
- constant-time lookup cuando corresponda;
- privacidad de metadata;
- revocación idempotente;
- invalidación de familias de refresh token cuando corresponda.

## Fuera de alcance

- JWT access token signing;
- endpoint controller;
- discovery metadata;
- resource server integration;
- event publication.

## Criterios de aceptación

- active semantics deterministas;
- revoked token inactivo;
- introspection model tipado;
- revocation request tipada;
- contracts neutrales;
- sin dependencia de JWT;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
