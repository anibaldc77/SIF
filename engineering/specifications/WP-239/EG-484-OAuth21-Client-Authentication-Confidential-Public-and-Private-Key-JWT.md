---
id: EG-484
title: Autenticación de clientes OAuth 2.1, clientes confidenciales/públicos y contratos private_key_jwt
summary: Define métodos de autenticación de cliente y fronteras neutrales para client secret, private_key_jwt y mTLS.
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
  - client-authentication
  - private-key-jwt
  - mtls
depends_on:
  - EG-483
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-484 — OAuth 2.1 Client Authentication

## Objetivo

Definir la autenticación de clientes sin acoplar Foundation a HTTP Basic, librerías JWT, certificados TLS o storage concreto.

## Métodos soportados a nivel arquitectónico

- none;
- client_secret;
- private_key_jwt;
- mTLS.

## Public vs Confidential

`OAuthClient::confidential()` continúa siendo la señal de clasificación del cliente.

Los clientes públicos pueden utilizar `none`.

Los clientes confidenciales requieren una estrategia de autenticación concreta definida por adapters/application layer.

## Authentication Request

`OAuthClientAuthenticationRequest` agrupa:

- client id;
- authentication method;
- credential opcional.

## Contracts

- `OAuthClientAuthenticatorInterface`;
- `OAuthClientSecretVerifierInterface`;
- `OAuthPrivateKeyJwtVerifierInterface`;
- `OAuthMutualTlsClientVerifierInterface`.

## Neutralidad

I4 no conoce:

- Authorization header;
- HTTP Basic;
- librerías JWT;
- OpenSSL concreto;
- variables de servidor TLS;
- reverse proxy;
- provider-specific SDKs.

## Seguridad

Los adapters productivos deberán aplicar:

- hash/secret verification seguro;
- audience/issuer/jti/exp para private_key_jwt;
- replay protection;
- certificate binding para mTLS;
- constant-time comparisons.

## Fuera de alcance

- implementación concreta de secret hashing;
- JWT parsing/signature;
- certificate validation;
- Dynamic Client Registration;
- token endpoint orchestration.

## Criterios de aceptación

- métodos tipados;
- public client none;
- confidential client model preservado;
- private_key_jwt detrás de contrato;
- mTLS detrás de contrato;
- infrastructure-neutral;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
