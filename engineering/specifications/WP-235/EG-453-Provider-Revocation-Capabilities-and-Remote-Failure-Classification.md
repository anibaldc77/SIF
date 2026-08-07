---
id: EG-453
title: Capacidades de revocación de proveedor y clasificación de fallas remotas
summary: Define capacidades explícitas por proveedor, outcomes de revocación remota y clasificación retryable/non-retryable sin acoplamiento a IdP concreto.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-235
tags:
  - security
  - federation
  - revocation
  - provider
  - remote-failure
depends_on:
  - EG-452
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-453 — Provider Revocation Capabilities y Remote Failure Classification

## Objetivo

Representar diferencias operativas entre proveedores de identidad sin introducir dependencias concretas en Foundation.

## Capacidades

`FederatedProviderRevocationCapability` define:

- revoke access token;
- revoke refresh token;
- end session;
- global logout.

Cada proveedor declara sólo las capacidades que realmente soporta.

## Fallas remotas

`FederatedRemoteFailureKind` clasifica:

- `transient`: retryable;
- `permanent`: no retryable;
- `unsupported`: capability no soportada.

## Adapter

`FederatedProviderRevocationAdapterInterface` ejecuta una capability remota. Foundation no define HTTP, endpoints ni credenciales.

## Seguridad

- no existe fallback silencioso;
- unsupported no se interpreta como éxito;
- sólo transient es retryable;
- Foundation permanece provider-neutral y transport-neutral.

## Criterios de aceptación

- capabilities explícitas;
- unsupported evita llamada remota;
- transient retryable;
- permanent no retryable;
- supported delega al adapter;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
