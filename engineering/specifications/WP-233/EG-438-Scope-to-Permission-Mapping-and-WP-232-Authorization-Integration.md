---
id: EG-438
title: Mapping de scopes a permissions e integración con autorización WP-232
summary: Define mapping explícito scope→permission y adaptación de claims OAuth a atributos de autorización sin convertir scopes en permisos implícitos.
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
  - scopes
  - permissions
  - authorization
depends_on:
  - EG-437
  - EG-432
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-438 — Scope-to-Permission Mapping e integración WP-232

## Objetivo

Integrar OAuth 2.0 Resource Server con Advanced Authorization sin equiparar automáticamente scopes OAuth con permissions de aplicación.

## ScopePermissionMap

El mapping es explícito.

Ejemplo:

- `invoice.read` → `invoice.read`
- `invoice.approve` → `invoice.approve`

Una aplicación también puede mapear un scope a múltiples permissions.

Un scope desconocido no concede permisos.

## OAuthPermissionResolver

Implementa `PermissionResolverInterface`.

Sólo produce permissions cuando:

- existe mapping explícito;
- el principal autenticado corresponde al `sub` del token validado.

Un mismatch subject/principal falla cerrado.

## Claims como atributos

`OAuthAccessTokenAuthorizationAttributes` namespacea los claims:

- `oauth.subject`;
- `oauth.scope.count`;
- `oauth.claim.<name>`.

Esto evita colisiones con atributos propios de la aplicación.

## OAuthAuthorizationContext

Expone:

- permissions derivados;
- atributos subject derivados.

No produce `AuthorizationDecision`.

La decisión continúa perteneciendo a WP-227/WP-232.

## Seguridad

- scope desconocido no es permission;
- subject mismatch no concede permissions;
- claims no mutan principal;
- `AuthenticationLevel` no se modifica;
- mapping no eleva privilegios implícitamente.

## Criterios de aceptación

- Mapping scope→permission explícito.
- Unknown scope fail-closed.
- Subject match obligatorio.
- Claims namespaced.
- Sin segundo decision engine.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
