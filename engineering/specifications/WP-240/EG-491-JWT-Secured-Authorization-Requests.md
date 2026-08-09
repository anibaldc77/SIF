---
id: EG-491
title: JWT Secured Authorization Requests
summary: Define request objects firmados para solicitudes OAuth, verificación, lifetime y replay boundaries sin acoplar Foundation a una librería JWT concreta.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-240
tags:
  - security
  - oauth
  - jar
  - jwt
  - request-object
depends_on:
  - EG-490
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-491 — JWT Secured Authorization Requests

## Objetivo

Agregar JWT Secured Authorization Requests sobre WP-239/WP-240 manteniendo firma, verificación y replay protection detrás de contratos.

## Request Object

`OAuthAuthorizationRequestObject` contiene:

- representación serializada;
- `OAuthAuthorizationRequest` tipada;
- issuer;
- audience;
- issuedAt;
- expiresAt;
- token id;
- key id opcional.

## Verification Result

`OAuthAuthorizationRequestObjectVerificationResult` devuelve la authorization request validada junto con metadata relevante.

## Contracts

- `OAuthAuthorizationRequestObjectSignerInterface`;
- `OAuthAuthorizationRequestObjectVerifierInterface`;
- `OAuthAuthorizationRequestObjectReplayStoreInterface`.

## Seguridad

Adapters productivos deberán:

- restringir algoritmos;
- validar firma;
- validar issuer/audience;
- validar `exp`/`iat`;
- validar `jti`;
- impedir replay cuando corresponda;
- resolver claves de cliente de forma confiable;
- comparar parámetros externos e internos según la política aplicable.

## Compatibilidad

JAR puede utilizarse junto con:

- PAR;
- Authorization Code;
- PKCE;
- RAR;
- OIDC.

## Neutralidad

Foundation no conoce:

- Firebase JWT;
- Lcobucci JWT;
- OpenSSL directo;
- JWKS endpoint;
- HTTP request parsing;
- base de datos;
- cache.

## Fuera de alcance

- RAR;
- DPoP;
- JWKS publication;
- request object encryption;
- HTTP endpoint implementation.

## Criterios de aceptación

- request object tipado;
- metadata temporal explícita;
- issuer/audience/jti explícitos;
- signer/verifier contracts;
- replay store contract;
- crypto-neutral;
- infrastructure-neutral;
- PHPUnit focalizado limpio;
- PHPStan limpio;
- Builder sin diagnósticos.
