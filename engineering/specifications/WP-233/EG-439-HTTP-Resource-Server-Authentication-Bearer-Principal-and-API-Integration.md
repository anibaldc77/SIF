---
id: EG-439
title: Autenticación HTTP Resource Server, Bearer principal e integración API
summary: Define orquestación Bearer HTTP-neutral, creación de AuthenticatedPrincipal y exposición del contexto OAuth de autorización.
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
  - resource-server
  - http
  - api
depends_on:
  - EG-438
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-439 — HTTP Resource Server Authentication e integración API

## Objetivo

Conectar extracción y validación Bearer con el `AuthenticatedPrincipal` existente de SIF y con el contexto de autorización de WP-232.

## Resource Server Authenticator

`OAuth2ResourceServerAuthenticator`:

1. extrae Bearer;
2. transforma errores de formato a `invalid_request`;
3. trata ausencia o token inválido como `invalid_token`;
4. valida el access token;
5. construye el principal autenticado.

No produce `Response`.

## BearerPrincipalFactory

Convierte `ValidatedAccessToken` al `AuthenticatedPrincipal` existente.

No introduce un segundo principal OAuth.

El método de autenticación se registra como `oauth2-bearer`.

## AuthenticationLevel

I7 utiliza un nivel explícito y estable para el principal Bearer.

El Resource Server no realiza step-up ni MFA.

## API Bridge

`ResourceServerApiBridge` combina:

- resultado de autenticación;
- contexto OAuth de autorización.

El contexto sólo existe cuando la autenticación fue exitosa.

## Frontera HTTP

La aplicación decide cómo convertir el failure en:

- status;
- body;
- headers;
- excepción;
- middleware response.

WP-233 sólo entrega el modelo canónico.

## Seguridad

- no crea sesiones;
- no escribe cookies;
- no redirige;
- no crea Response;
- no expone access token;
- no crea contexto de autorización para una autenticación fallida.

## Criterios de aceptación

- Bearer válido crea principal.
- Missing token → invalid_token.
- Malformed header → invalid_request.
- Invalid token → 401.
- Contexto de autorización sólo tras éxito.
- Sin Session/Response/cookie.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
