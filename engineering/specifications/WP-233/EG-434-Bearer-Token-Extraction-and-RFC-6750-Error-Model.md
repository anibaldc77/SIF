---
id: EG-434
title: Extracción Bearer y modelo de errores RFC 6750
summary: Define extracción segura del Authorization header, errores Bearer y challenges HTTP para OAuth 2.0 Resource Server.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-233
tags:
  - security
  - oauth2
  - bearer
  - rfc6750
depends_on:
  - EG-433
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-434 — Extracción Bearer y errores RFC 6750

## Objetivo

Incorporar extracción Bearer y un modelo canónico de fallas compatible con RFC 6750 sin mezclar aún validación criptográfica.

## Extracción

`BearerTokenExtractor`:

- acepta el esquema Bearer de forma case-insensitive;
- considera header vacío como ausencia de credencial;
- rechaza esquemas distintos;
- rechaza valores vacíos o con whitespace interno;
- devuelve `AccessToken`.

No intenta validar firma, expiración ni scopes.

## Errores

Se modelan:

- `invalid_request`;
- `invalid_token`;
- `insufficient_scope`.

## Status HTTP

La factory establece:

- `invalid_request` → 400;
- `invalid_token` → 401;
- `insufficient_scope` → 403.

## WWW-Authenticate

`BearerChallenge` produce el valor del header `WWW-Authenticate`.

No contiene access tokens.

## Seguridad

Los errores no deben incluir:

- token completo;
- fragmentos del token;
- Authorization header;
- secretos de validación.

## Separaciones obligatorias

I2 no:

- valida JWT;
- consulta JWKS;
- realiza introspection;
- crea principal;
- modifica sesión;
- emite tokens.

## Criterios de aceptación

- Extracción Bearer correcta.
- Header vacío tratado como no credential.
- Malformed header rechazado.
- Errores y status canónicos.
- Challenge sin secretos.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
