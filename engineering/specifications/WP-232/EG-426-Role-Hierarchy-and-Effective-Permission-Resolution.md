---
id: EG-426
title: Jerarquía de roles y resolución efectiva de permisos
summary: Define composición de roles heredados y permisos efectivos sin duplicar el motor de decisiones de WP-227.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-232
tags:
  - security
  - authorization
  - roles
  - permissions
  - hierarchy
depends_on:
  - EG-425
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-426 — Jerarquía de roles y resolución efectiva de permisos

## Objetivo

Resolver permisos efectivos a partir de roles directos, herencia de roles y permisos directos del principal.

## RoleDefinition

Cada definición contiene:

- identificador de rol;
- permisos propios;
- roles heredados.

La definición no decide autorización.

## RoleHierarchy

La jerarquía:

- normaliza roles por identificador;
- rechaza definiciones duplicadas;
- detecta ciclos;
- expande transitivamente roles heredados;
- tolera referencias a roles no definidos sin conceder permisos implícitos.

## Permisos efectivos

`EffectivePermissionResolver` combina:

- permisos directos;
- permisos de roles directos;
- permisos de roles heredados.

El resultado es un `PermissionSet` determinístico y sin duplicados.

## Resolución del principal

`PrincipalAuthorizationGrantResolver` coordina:

- `RoleResolverInterface`;
- `PermissionResolverInterface`;
- `EffectivePermissionResolver`.

Produce datos efectivos, no una decisión.

## Seguridad

Un rol desconocido no concede permisos.

Una jerarquía cíclica se rechaza durante construcción.

Ningún resolver de I2 puede:

- devolver `AuthorizationDecision`;
- permitir o denegar;
- mutar principal;
- modificar sesión;
- alterar `AuthenticationLevel`.

## Compatibilidad

El motor de policies y decisiones de WP-227 continúa siendo el único responsable de autorizar.

## Criterios de aceptación

- Herencia transitiva determinística.
- Detección de ciclos.
- Composición de permisos directos e heredados.
- Roles desconocidos fail-closed.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
