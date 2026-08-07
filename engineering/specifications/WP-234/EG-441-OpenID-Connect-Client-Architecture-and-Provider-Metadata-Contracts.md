---
id: EG-441
title: Arquitectura OpenID Connect Client y contratos de provider metadata
summary: Define la base de autenticación federada OIDC, metadata del proveedor, registro de cliente, state y nonce sin acoplar SIF a un proveedor concreto.
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
  - federation
  - identity-provider
depends_on:
  - EG-440
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-441 — OpenID Connect Client y provider metadata

## Objetivo

Iniciar WP-234 definiendo la arquitectura de un OpenID Connect Client para autenticación federada.

SIF actuará como Relying Party / OIDC Client frente a Identity Providers externos.

## Reutilización

WP-234 reutiliza:

- identidad y principal de WP-227;
- sesiones de WP-226;
- JWT/JWKS de WP-233;
- autorización de WP-232.

No duplica estos subsistemas.

## Provider metadata

`OidcProviderMetadata` representa los datos relevantes de discovery:

- issuer;
- authorization endpoint;
- token endpoint;
- JWKS URI;
- response types;
- subject types;
- algoritmos de firma de ID Token;
- UserInfo opcional.

## Provider contract

`OidcProviderMetadataProviderInterface` mantiene discovery detrás de contrato.

El Core no realiza HTTP.

## Client registration

`OidcClientRegistration` contiene:

- client id;
- redirect URI;
- condición public/confidential.

Los secretos de cliente no forman parte de este value object.

Su almacenamiento y exposición requerirán un contrato seguro posterior.

## State y nonce

`OidcState` y `OidcNonce` son tipos distintos.

No son strings intercambiables.

Los generadores quedan detrás de contratos para mantener separada la política criptográfica.

## Compatibilidad

La arquitectura admite proveedores OIDC estándar como:

- Keycloak;
- Microsoft Entra ID;
- Auth0;
- Okta;
- otros proveedores compatibles.

No existe dependencia directa de ninguno.

## Fuera de alcance de I1

I1 no implementa:

- Authorization Code flow;
- PKCE;
- token exchange;
- validación de ID Token;
- callback HTTP;
- creación de sesión;
- logout federado.

## Criterios de aceptación

- Provider metadata explícita.
- Client registration sin secretos.
- State y nonce separados.
- Discovery transport-neutral.
- Sin duplicar JWT/JWKS/OAuth.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
