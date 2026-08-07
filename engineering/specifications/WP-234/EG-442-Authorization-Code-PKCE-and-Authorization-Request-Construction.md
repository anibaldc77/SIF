---
id: EG-442
title: Authorization Code, PKCE y construcción de authorization request
summary: Define state, nonce, PKCE S256, transaction correlation y construcción del request de autorización OIDC sin realizar todavía token exchange.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-234
tags:
  - security
  - oidc
  - authorization-code
  - pkce
depends_on:
  - EG-441
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-442 — Authorization Code + PKCE

## Objetivo

Incorporar la construcción del Authorization Request OIDC mediante Authorization Code y PKCE S256.

## PKCE

`PkceCodeVerifier` aplica los límites RFC 7636:

- longitud 43..128;
- caracteres unreserved.

`NativeS256PkceCodeChallengeFactory` calcula:

`BASE64URL(SHA256(code_verifier))`

Sólo se soporta `S256`.

## Authorization Request

El request incluye:

- response_type=code;
- client_id;
- redirect_uri;
- scope con `openid`;
- state;
- nonce;
- code_challenge;
- code_challenge_method=S256.

## Transaction

`OidcAuthorizationTransaction` retiene:

- state;
- nonce;
- code verifier;
- request.

El `code_verifier` no se incluye en el request de autorización.

## Seguridad

- state y nonce permanecen separados;
- PKCE es obligatorio en la arquitectura;
- el verifier no se transmite al authorization endpoint;
- no se almacena client secret;
- no se crea sesión todavía;
- no se realiza token exchange en I2.

## Compatibilidad

El request sigue OIDC/OAuth estándar y no depende de Keycloak ni otro proveedor.

## Criterios de aceptación

- challenge S256 correcto.
- openid obligatorio.
- state/nonce correlacionados.
- verifier retenido fuera del request.
- sin token exchange.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
