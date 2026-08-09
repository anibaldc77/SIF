---
id: EG-483
title: Access Token, Refresh Token, Lifetime, Rotation and Token Issuance en OAuth 2.1
summary: Define tokens tipados, lifetime, familias de refresh token, rotación y contratos neutrales de emisión, persistencia y revocación.
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
  - access-token
  - refresh-token
  - rotation
depends_on:
  - EG-482
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-483 — OAuth 2.1 Access Token, Refresh Token, Lifetime, Rotation and Issuance

## Objetivo

Agregar el modelo de tokens del Authorization Server sin fijar formato JWT, algoritmo de firma ni storage concreto.

## Access Token

`OAuthAccessToken` contiene:

- valor opaco;
- client id;
- scopes;
- issuedAt;
- expiresAt.

## Refresh Token

`OAuthRefreshToken` contiene:

- valor opaco;
- client id;
- family id;
- scopes;
- issuedAt;
- expiresAt;
- token reemplazado opcional.

## Rotation family

`OAuthRefreshTokenFamilyId` permite agrupar una cadena de rotación.

La persistencia puede revocar la familia completa ante reuse detection u otro evento de seguridad.

## Token Pair

`OAuthTokenPair` agrupa access token y refresh token opcional.

## Contracts

- `OAuthTokenIssuerInterface`;
- `OAuthAccessTokenRepositoryInterface`;
- `OAuthRefreshTokenRepositoryInterface`;
- `OAuthRefreshTokenRotatorInterface`.

## Neutralidad

I3 no prescribe:

- JWT;
- JWE;
- opaque token format;
- firma;
- base de datos;
- cache;
- HTTP response.

## Seguridad

- lifetime explícito;
- expiración determinista;
- familias de refresh token;
- revocación individual;
- revocación de familia;
- rotación detrás de contrato.

## Fuera de alcance

- reuse detection implementation;
- introspection;
- RFC 7009;
- JWT access token signing;
- client authentication;
- token endpoint orchestration.

## Criterios de aceptación

- access token temporal;
- refresh token temporal;
- rotation family explícita;
- token pair;
- repositories neutrales;
- issuer/rotator neutrales;
- sin formato JWT obligatorio;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
