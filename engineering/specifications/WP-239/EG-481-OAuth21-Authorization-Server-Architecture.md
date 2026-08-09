---
id: EG-481
title: Arquitectura de OAuth 2.1 Authorization Server
summary: Define la foundation contract-first del servidor de autorización OAuth 2.1, su modelo de clientes, scopes y requests, sin acoplamiento a transporte, storage o proveedor.
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
  - oauth2.1
  - authorization-server
  - identity
depends_on:
  - EG-480
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-481 — OAuth 2.1 Authorization Server Architecture

## Objetivo

Iniciar WP-239 con una foundation OAuth 2.1 contract-first, desacoplada de HTTP, storage, proveedor de identidad y mecanismos concretos de firma.

## Cliente OAuth

`OAuthClient` representa:

- client id;
- nombre;
- confidential/public;
- redirect URIs;
- scopes permitidos.

La autenticación concreta de clientes queda fuera de I1.

## Authorization request

`OAuthAuthorizationRequest` representa:

- client id;
- redirect URI;
- response type;
- scopes;
- state opcional.

PKCE, nonce y consent se incorporarán en incrementos posteriores.

## Token request

`OAuthTokenRequest` representa:

- grant type;
- client id;
- parámetros específicos del grant.

I1 no ejecuta grants.

## Contracts

- `AuthorizationServerInterface`;
- `OAuthClientRepositoryInterface`;
- `OAuthScopeRepositoryInterface`;
- `OAuthAuthorizationRequestValidatorInterface`;
- `OAuthTokenRequestValidatorInterface`.

## Neutralidad

Foundation no conoce:

- controllers HTTP;
- PSR-7;
- sesiones;
- base de datos;
- Redis;
- proveedores externos;
- librerías JWT concretas.

## Compatibilidad

La arquitectura queda preparada para interoperar con:

- Authentication/Authorization;
- OIDC;
- OAuth2 Resource Server;
- Identity Providers;
- Audit/Event Dispatcher.

## Fuera de alcance de I1

- Authorization Code;
- PKCE;
- Refresh Token;
- Client Credentials;
- Device Authorization;
- token signing;
- introspection;
- revocation;
- consent engine;
- client authentication.

## Criterios de aceptación

- client model explícito;
- requests tipados;
- contracts neutrales;
- provider-neutral;
- sin traducción HTTP;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
