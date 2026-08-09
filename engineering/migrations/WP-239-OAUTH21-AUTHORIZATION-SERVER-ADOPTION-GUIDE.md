---
id: WP-239-ADOPTION-GUIDE
title: Guía de adopción del OAuth 2.1 Authorization Server
summary: Describe cómo integrar WP-239 con HTTP, storage, cryptography, Authentication, OIDC y Resource Server.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-239
tags:
  - security
  - oauth
  - authorization-server
  - adoption
depends_on:
  - EG-488
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Guía de adopción — WP-239

## Objetivo

Integrar el Authorization Server OAuth 2.1 de SIF sin contaminar Foundation con infraestructura concreta.

## Client Repository

Implementar `OAuthClientRepositoryInterface` con storage productivo.

## Authorization Code

Implementar `OAuthAuthorizationCodeRepositoryInterface` con consumo atómico y de un solo uso.

## PKCE

Utilizar `OAuthPkceVerifierInterface` y exigir S256 para clientes públicos.

## Tokens

Implementar:

- `OAuthTokenIssuerInterface`;
- `OAuthAccessTokenRepositoryInterface`;
- `OAuthRefreshTokenRepositoryInterface`;
- `OAuthRefreshTokenRotatorInterface`.

La rotación debe considerar reuse detection y revocación de familia.

## Client Authentication

Implementar:

- `OAuthClientAuthenticatorInterface`;
- `OAuthClientSecretVerifierInterface`;
- `OAuthPrivateKeyJwtVerifierInterface`;
- `OAuthMutualTlsClientVerifierInterface`.

## Introspection / Revocation

Implementar:

- `OAuthTokenIntrospectorInterface`;
- `OAuthTokenRevokerInterface`;
- `OAuthTokenStatusProviderInterface`.

## JWT

Si se usan JWT access tokens, implementar:

- `OAuthJwtClaimsFactoryInterface`;
- `OAuthJwtAccessTokenSignerInterface`;
- `OAuthSigningKeyProviderInterface`.

El manejo de claves privadas debe quedar fuera de Foundation.

## Device Flow

Implementar `OAuthDeviceAuthorizationRepositoryInterface` y `OAuthDeviceAuthorizationApproverInterface`.

El polling HTTP debe respetar `pollIntervalSeconds()` y rate limiting.

## Client Credentials

Implementar:

- `OAuthMachineIdentityResolverInterface`;
- `OAuthClientCredentialsTokenIssuerInterface`.

Los machine principals deben mantenerse separados de usuarios humanos.

## Integración con OIDC

OIDC puede construir sobre esta foundation para `nonce`, ID Tokens, UserInfo y discovery.

## Integración con Resource Server

Los access tokens emitidos por WP-239 pueden ser consumidos por la foundation OAuth2 Resource Server ya existente.

## Seguridad mínima

- PKCE S256;
- redirect URI exacta;
- authorization codes one-time;
- confidential client authentication;
- refresh token rotation;
- token revocation;
- key rotation;
- audience validation;
- replay protection;
- rate limiting;
- audit/observability.
