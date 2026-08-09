---
id: EG-488
title: Cierre de producto del OAuth 2.1 Authorization Server
summary: Consolida arquitectura, Authorization Code, PKCE, tokens, client authentication, introspection, revocation, JWT, device flow y machine identity como foundation OAuth 2.1 enterprise-neutral.
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
  - oauth2.1
  - authorization-server
  - product-completion
depends_on:
  - EG-487
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-488 — OAuth 2.1 Authorization Server Product Completion

## Objetivo

Cerrar WP-239 como foundation empresarial de Authorization Server OAuth 2.1 manteniendo separación estricta respecto de HTTP, storage, cryptography providers e identity providers concretos.

## Capacidades consolidadas

- client model;
- redirect URIs;
- scopes;
- authorization requests;
- Authorization Code;
- PKCE S256;
- access tokens;
- refresh tokens;
- rotation families;
- token issuance boundaries;
- public/confidential client model;
- client secret/private_key_jwt/mTLS contracts;
- introspection;
- revocation;
- active token semantics;
- JWT claims;
- signing metadata;
- signing key rotation;
- device authorization;
- client credentials;
- machine identity.

## Separación de responsabilidades

### HTTP

Foundation define modelos y contratos; controllers/endpoints pertenecen a adapters/application layer.

### Storage

Repositorios permanecen contract-first.

### Cryptography

Firma y key management permanecen detrás de interfaces.

### Identity Providers

WP-239 no depende de Keycloak, Entra ID, Okta, Auth0 ni otro proveedor.

## Orden conceptual recomendado

1. resolver cliente;
2. validar redirect URI y scopes;
3. autenticar cliente cuando corresponda;
4. procesar grant;
5. emitir/rotar tokens;
6. evaluar token status;
7. introspectar/revocar;
8. firmar tokens cuando se use JWT;
9. resolver device/machine identity;
10. traducir a HTTP en adapters externos.

## Riesgos residuales

Adapters productivos deberán implementar:

- HTTP endpoint semantics;
- OAuth error translation;
- consent;
- replay protection;
- secret hashing;
- private_key_jwt validation;
- mTLS certificate binding;
- persistence atomicity;
- refresh token reuse detection;
- JWKS publication;
- rate limiting;
- observability;
- audit events.

## Criterios de aceptación

- composición I1-I7;
- separación de concerns;
- infrastructure-neutral;
- provider-neutral;
- integration tests limpios;
- PHPStan limpio;
- Builder sin diagnósticos.
