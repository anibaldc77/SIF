---
id: WP-233-MIGRATION-GUIDE
title: Guía de adopción OAuth 2.0 Resource Server
summary: Describe la adopción gradual de Bearer, JWT/JWKS, introspection y mapping de scopes en aplicaciones SIF.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-233
tags:
  - security
  - oauth2
  - resource-server
  - migration
depends_on:
  - EG-440
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Guía de adopción — WP-233 OAuth 2.0 Resource Server

## Impacto

WP-233 es opt-in.

Las aplicaciones con autenticación por sesión pueden continuar sin cambios.

## JWT

Para JWT:

1. implementar `JwtParserInterface`;
2. implementar `JwkSignatureVerifierInterface`;
3. configurar `JwkSetProviderInterface`;
4. construir `JwkResolver`;
5. construir `JwksJwtSignatureVerifier`;
6. configurar `JwtValidationPolicy`;
7. utilizar `JwtAccessTokenValidator`.

## Tokens opacos

Para introspection:

1. implementar `TokenIntrospectorInterface`;
2. proteger las credenciales utilizadas por el adapter;
3. construir `OpaqueAccessTokenValidator`.

## HTTP

Utilizar:

- `BearerTokenExtractor`;
- `OAuth2ResourceServerAuthenticator`;
- `ResourceServerApiBridge`.

La aplicación convierte los failures en sus respuestas HTTP habituales.

## Scopes

Definir explícitamente `ScopePermissionMap`.

No asumir que un scope externo tiene idéntica semántica a un permission interno.

## Keycloak

Keycloak puede integrarse de dos maneras:

- JWT firmado + JWKS;
- token introspection.

WP-233 no requiere código específico de Keycloak en el Core.

## Seguridad operacional

En adapters productivos:

- exigir HTTPS/TLS;
- definir timeout;
- limitar tamaño de respuestas;
- cachear JWKS con política explícita;
- proteger client secrets de introspection;
- registrar sólo fingerprints, nunca tokens completos.
