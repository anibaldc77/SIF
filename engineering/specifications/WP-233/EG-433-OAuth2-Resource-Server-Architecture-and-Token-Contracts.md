---
id: EG-433
title: Arquitectura OAuth 2.0 Resource Server y contratos de access token
summary: Define el modelo neutral de access token, scopes, validación y extracción Bearer para iniciar WP-233 sin acoplar el Core a JWT, JWKS u OIDC.
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
  - access-token
depends_on:
  - EG-432
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-433 — OAuth 2.0 Resource Server y contratos de access token

## Objetivo

Definir la arquitectura base para que SIF actúe como OAuth 2.0 Resource Server.

WP-233 valida credenciales emitidas por un Authorization Server externo. No emite access tokens.

## AccessToken

`AccessToken` representa material sensible y opaco.

El Core no presupone que el token sea JWT.

El valor:

- se mantiene redactado;
- no se serializa;
- sólo puede exponerse mediante callback explícito;
- ofrece fingerprint SHA-256 para diagnósticos seguros.

## ValidatedAccessToken

Representa el resultado exitoso de una validación e incluye:

- subject;
- scopes;
- expiración;
- fecha de emisión opcional;
- atributos escalares normalizados.

## Scopes

`ScopeSet` mantiene scopes deduplicados y determinísticos.

Los scopes no son automáticamente permisos de aplicación. La traducción scope → permission pertenece a una capa posterior de integración con WP-232.

## Contratos

`AccessTokenValidatorInterface` abstrae la validación.

Implementaciones posteriores podrán utilizar:

- JWT firmado;
- JWKS;
- introspection;
- adapters de proveedores externos.

`BearerTokenExtractorInterface` abstrae la extracción desde el transporte.

## Separaciones obligatorias

I1 no debe:

- emitir tokens;
- implementar Authorization Code;
- implementar Refresh Token;
- depender de OpenID Connect;
- depender de Keycloak;
- asumir JWT;
- realizar HTTP saliente;
- modificar sesiones.

## Criterios de aceptación

- Token sensible y redactado.
- Validación neutral respecto del formato.
- Scopes determinísticos.
- Resultado validado con expiración.
- Sin emisión de tokens.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
