---
id: EG-437
title: OAuth 2.0 Token Introspection y validación de tokens opacos
summary: Define introspection neutral de transporte, mapping de respuesta y adaptación de tokens opacos al contrato ValidatedAccessToken.
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
  - introspection
  - opaque-token
depends_on:
  - EG-436
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-437 — Token Introspection y tokens opacos

## Objetivo

Incorporar validación de access tokens opacos mediante un contrato de introspection sin acoplar el Core al transporte HTTP ni al Authorization Server.

## Contrato

`TokenIntrospectorInterface` recibe un `AccessToken` y devuelve `TokenIntrospectionResult`.

La implementación concreta puede utilizar:

- RFC 7662 HTTP introspection;
- cache;
- proxy interno;
- adapter de Keycloak;
- otro proveedor.

## Mapping

`ArrayTokenIntrospectionMapper` reconoce:

- `active`;
- `sub`;
- `scope`;
- `exp`;
- `iat`.

Los campos adicionales sólo se copian si son escalares o null.

## OpaqueAccessTokenValidator

Un token sólo se acepta cuando:

- `active=true`;
- existe subject;
- no está expirado.

El resultado se adapta a `ValidatedAccessToken`.

## Seguridad

- `active=false` falla cerrado;
- respuesta activa sin `sub` es inválida;
- expiración vencida falla cerrado;
- no se exponen secretos;
- no se modela `client_secret` en el Core;
- el contrato no realiza HTTP.

## Criterios de aceptación

- Mapping de introspection.
- Inactive token rechazado.
- Active token adaptado correctamente.
- Expired token rechazado.
- Transporte neutral.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
