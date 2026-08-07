---
id: WP-234-MIGRATION-GUIDE
title: Guía de adopción OpenID Connect y autenticación federada
summary: Describe la integración gradual de proveedores OIDC con aplicaciones SIF.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-234
tags:
  - security
  - oidc
  - federation
  - migration
depends_on:
  - EG-448
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Guía de adopción — WP-234

## Impacto

WP-234 es opt-in.

Las aplicaciones existentes pueden continuar utilizando password authentication, MFA, persistent authentication y sesiones sin OIDC.

## Provider metadata

Implementar `OidcProviderMetadataProviderInterface`.

Para proveedores estándar puede obtenerse desde:

`/.well-known/openid-configuration`

El adapter HTTP pertenece a infraestructura, no a Foundation.

## Authorization Code + PKCE

Utilizar:

- `OidcAuthorizationRequestFactory`;
- `NativeS256PkceCodeChallengeFactory`;
- `OidcHttpLoginStartService`.

Persistir temporalmente la `OidcAuthorizationTransaction` de forma segura para recuperar state, nonce y code verifier en el callback.

## Callback

Construir `OidcAuthorizationCallback` con:

- state;
- authorization code.

Luego delegar en `FederatedLoginOrchestrator`.

## Token exchange

Implementar `OidcTokenExchangerInterface`.

Para clientes confidenciales, obtener `OidcClientSecret` desde un secret store.

Nunca almacenar el secreto dentro del source code o `OidcClientRegistration`.

## ID Token

Implementar `OidcIdTokenParserInterface` y reutilizar JWKS/crypto de WP-233.

Configurar:

- issuer exacto;
- client id;
- allow-list de algoritmos;
- clock skew razonable.

## Account linking

Implementar:

- `FederatedIdentityLinkResolverInterface`;
- `FederatedIdentityProvisionerInterface`.

La clave externa debe utilizar `issuer + subject`.

No vincular automáticamente por email.

## Session

Implementar `FederatedSessionEstablisherInterface` como adapter al subsistema de sesiones existente.

## Keycloak

Keycloak se integra mediante OIDC estándar:

- discovery endpoint;
- Authorization Code;
- PKCE;
- token endpoint;
- JWKS;
- ID Token;
- end-session endpoint.

No se requiere código Keycloak dentro de Foundation.

## Seguridad operacional

- exigir HTTPS;
- validar redirect URIs;
- proteger client secrets;
- no loguear codes/tokens;
- rotar secretos;
- cachear discovery/JWKS con límites;
- mantener provisioning automático deshabilitado salvo necesidad explícita.
