---
id: EG-487
title: Device Authorization Flow, Client Credentials y Machine Identity en OAuth 2.1
summary: Define device codes, user codes, autorización diferida y machine identities para client credentials sin acoplar Foundation a polling HTTP, UI o proveedores.
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
  - device-flow
  - client-credentials
  - machine-identity
depends_on:
  - EG-486
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-487 — OAuth 2.1 Device Authorization, Client Credentials and Machine Identity

## Objetivo

Agregar modelos para dispositivos sin navegador y machine-to-machine, manteniendo la ejecución de polling, UI y transporte fuera de Foundation.

## Device Authorization

`OAuthDeviceCode` modela valor, client id, issuedAt, expiresAt y polling interval.

`OAuthUserCode` representa el código introducido por el usuario.

`OAuthDeviceAuthorization` representa device code, user code, scopes, status y subject opcional.

Status soportados: pending, approved y denied.

## Client Credentials y Machine Identity

`OAuthClientCredentialsGrant` representa client id y scopes.

`OAuthMachinePrincipal` representa una identidad de servicio explícita y no un usuario humano.

## Contracts

- `OAuthDeviceAuthorizationRepositoryInterface`;
- `OAuthDeviceAuthorizationApproverInterface`;
- `OAuthMachineIdentityResolverInterface`;
- `OAuthClientCredentialsTokenIssuerInterface`.

## Neutralidad

I7 no conoce polling HTTP, timers, controllers, UI, scheduler ni proveedores concretos.

## Criterios de aceptación

- device code temporal;
- polling interval explícito;
- user code tipado;
- status explícito;
- client credentials tipado;
- machine principal explícito;
- contracts neutrales;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
