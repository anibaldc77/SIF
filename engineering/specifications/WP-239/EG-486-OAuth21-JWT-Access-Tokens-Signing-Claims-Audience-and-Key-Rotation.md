---
id: EG-486
title: JWT Access Tokens, firma, claims, audience y rotación de claves en OAuth 2.1
summary: Define claims JWT, metadata de firma y contratos neutrales de signing y key rotation sin imponer una librería criptográfica concreta.
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
  - jwt
  - signing
  - key-rotation
depends_on:
  - EG-485
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-486 — OAuth 2.1 JWT Access Tokens, Signing, Claims, Audience and Key Rotation

## Objetivo

Agregar una representación JWT opcional para access tokens manteniendo contratos independientes de librerías criptográficas y almacenamiento de claves.

## Claims

`OAuthJwtClaims` contiene:

- issuer;
- subject;
- audience;
- client id;
- scopes;
- issuedAt;
- expiresAt;
- token id.

## Signing Key

`OAuthSigningKey` contiene:

- key id (`kid`);
- algorithm;
- material reference opaca.

Foundation no almacena directamente material criptográfico.

## Signed Access Token

`OAuthSignedAccessToken` conserva:

- token serializado;
- kid;
- algorithm;
- claims.

## Contracts

- `OAuthJwtAccessTokenSignerInterface`;
- `OAuthSigningKeyProviderInterface`;
- `OAuthJwtClaimsFactoryInterface`.

## Key Rotation

`OAuthSigningKeyProviderInterface` expone:

- `current()` para emisión;
- `find(kid)` para resolución histórica.

Esto permite rotar claves sin invalidar inmediatamente tokens aún vigentes.

## Neutralidad

I6 no prescribe:

- Firebase JWT;
- Lcobucci JWT;
- OpenSSL directo;
- HSM;
- Vault;
- JWKS endpoint;
- HTTP publication.

## Seguridad

Adapters productivos deberán:

- restringir algorithms permitidos;
- proteger material privado;
- validar issuer/audience;
- mantener `kid` único;
- rotar claves de manera controlada;
- conservar claves públicas/históricas durante la ventana necesaria.

## Fuera de alcance

- JWKS publication endpoint;
- JWE;
- DPoP;
- certificate-bound tokens;
- token endpoint orchestration.

## Criterios de aceptación

- claims tipados;
- audience explícita;
- kid/algorithm explícitos;
- signer neutral;
- key provider con current/find;
- sin dependencia de librería JWT;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
