---
id: EG-440
title: Cierre de producto OAuth 2.0 Resource Server
summary: Define los invariantes finales y criterios end-to-end de WP-233 para JWT, JWKS, introspection, scopes, principal Bearer e integración API.
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
  - jwt
  - jwks
  - introspection
  - product-completion
depends_on:
  - EG-439
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-440 — Cierre de producto OAuth 2.0 Resource Server

## Objetivo

Cerrar WP-233 validando el producto completo de Resource Server sobre los contratos de seguridad existentes de SIF.

## Invariantes finales

- `AccessToken` permanece opaco y sensible.
- El Core no emite access tokens.
- Bearer extraction y validación permanecen separadas.
- JWT es una implementación del contrato neutral.
- Algoritmos JWT se aceptan sólo mediante allow-list.
- issuer, audience, exp y nbf se validan.
- JWKS resuelve por kid y refresca de forma controlada.
- kid desconocido falla cerrado.
- Introspection soporta tokens opacos.
- JWT y opaque tokens convergen en `ValidatedAccessToken`.
- scopes no son permissions implícitos.
- mapping scope→permission es explícito.
- Bearer crea el `AuthenticatedPrincipal` existente.
- Resource Server no crea sesiones ni cookies.
- la integración API no crea otro decision engine.

## Compatibilidad de proveedores

Los contratos admiten adapters para:

- Keycloak;
- Microsoft Entra ID;
- Auth0;
- Okta;
- servidores OAuth propios;
- otros Authorization Servers compatibles.

La compatibilidad no implica dependencia directa.

## Fuera de alcance

WP-233 no implementa:

- Authorization Server;
- Authorization Code;
- PKCE;
- Client Credentials issuance;
- Refresh Token issuance;
- OpenID Connect login;
- UserInfo endpoint.

## Criterios de aceptación

- JWT end-to-end autenticado.
- JWKS fail-closed.
- opaque introspection end-to-end.
- scopes explícitamente mapeados.
- principal Bearer existente.
- sin emisión de token ni sesión.
- suite completa sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
