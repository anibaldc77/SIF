---
id: EG-449
title: Arquitectura de operaciones de seguridad federada y contratos de revocación
summary: Define scopes, razones, requests, resultados y contratos neutrales para revocar sesiones, credenciales externas y vínculos federados.
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
  - operations
depends_on:
  - EG-448
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-449 — Federated Security Operations y Revocation Contracts

## Objetivo

Iniciar WP-235 definiendo una arquitectura explícita para operaciones de seguridad posteriores al login federado.

## Operaciones cubiertas

La arquitectura contempla:

- revocación de sesiones locales;
- revocación de credenciales externas;
- desvinculación de identidad federada;
- revocación coordinada de todos los alcances.

## Scope

`FederatedRevocationScope` expresa intención operativa:

- `local_sessions`;
- `provider_credentials`;
- `identity_link`;
- `all`.

No existe revocación implícita.

## Reason

`FederatedRevocationReason` requiere código estructurado y permite detalle acotado.

Ejemplos:

- `user.requested`;
- `security.incident`;
- `administrator.action`;
- `provider.compromise`;
- `account.disabled`.

## Request

`FederatedRevocationRequest` mantiene separadas:

- identidad local;
- identidad federada;
- scope;
- reason.

## Contratos

### FederatedSessionRevokerInterface

Integra con el subsistema de sesión existente.

### FederatedProviderCredentialRevokerInterface

Permite adapters para revocación de credenciales/tokens en proveedores externos.

### FederatedIdentityLinkRevokerInterface

Permite eliminar o invalidar el vínculo persistido.

### FederatedSecurityOperationEventPublisherInterface

Publica eventos operativos auditables.

## Seguridad

- Foundation no destruye sesiones directamente;
- Foundation no realiza HTTP;
- no contiene SQL;
- no incluye código específico de Keycloak;
- no transporta access/refresh/id tokens en eventos;
- revocaciones se ejecutarán sólo mediante contratos explícitos.

## Compatibilidad

Los adapters externos pueden implementar operaciones contra proveedores como:

- Keycloak;
- Microsoft Entra ID;
- Auth0;
- Okta;
- cualquier proveedor compatible.

La abstracción central no depende de ninguno.

## Fuera de alcance de I1

I1 no implementa:

- orquestación de revocación;
- retries;
- compensación;
- persistencia;
- revocación remota HTTP;
- CLI;
- HTTP controllers;
- políticas temporales.

## Criterios de aceptación

- scopes explícitos;
- reason tipado;
- request local/federado separado;
- contratos storage-neutral;
- contratos provider-neutral;
- eventos sin secretos;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
