---
id: EG-482
title: Authorization Code, PKCE y validación estricta de Redirect URI en OAuth 2.1
summary: Define authorization codes tipados, PKCE S256, persistencia/consumo de códigos y validación exacta de redirect URIs.
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
  - authorization-code
  - pkce
  - redirect-uri
depends_on:
  - EG-481
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-482 — OAuth 2.1 Authorization Code, PKCE and Redirect URI Validation

## Objetivo

Incorporar el modelo de Authorization Code y PKCE requerido por OAuth 2.1, manteniendo la emisión de tokens fuera de I2.

## Authorization Code

`OAuthAuthorizationCode` contiene:

- valor opaco;
- client id;
- redirect URI;
- scopes;
- issuedAt;
- expiresAt;
- code challenge opcional;
- challenge method opcional.

## PKCE

I2 soporta exclusivamente `S256`.

`OAuthPkceVerifier` valida el formato y longitud del code verifier.

`DefaultOAuthPkceVerifier` calcula SHA-256 y Base64URL sin padding.

## Redirect URI

`OAuthRedirectUriValidator` exige igualdad exacta contra las redirect URIs registradas en el cliente.

No se permiten:

- wildcard;
- prefix matching;
- normalización permisiva.

## Code repository

`OAuthAuthorizationCodeRepositoryInterface` abstrae:

- save;
- find;
- consume.

La implementación concreta debe garantizar consumo de un solo uso.

## Seguridad

- authorization codes temporales;
- PKCE S256;
- redirect URI exacta;
- storage-neutral;
- provider-neutral.

## Fuera de alcance

- access token issuance;
- refresh tokens;
- client authentication;
- consent persistence;
- token endpoint orchestration.

## Criterios de aceptación

- lifetime explícito;
- PKCE S256 válido;
- verifier incorrecto rechazado;
- redirect URI exacta;
- mismatch rechazado;
- repository neutral;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
