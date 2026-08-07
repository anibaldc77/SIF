---
id: EG-427
title: Requisitos de autorización y composición de policies RBAC
summary: Define requisitos declarativos de permiso y rol y su composición sobre grants efectivos sin duplicar el decision engine de WP-227.
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
  - rbac
  - requirements
  - policy
depends_on:
  - EG-426
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-427 — Requisitos de autorización y composición RBAC

## Objetivo

Incorporar requisitos declarativos que puedan ser evaluados por policies construidas sobre roles y permisos efectivos.

## Requisitos

Se incorporan:

- `PermissionRequirement`;
- `RoleRequirement`;
- `AllOfRequirement`;
- `AnyOfRequirement`;
- `AuthorizationRequirementSet`.

Cada requisito responde si sus datos de autorización satisfacen una condición.

No produce `AuthorizationDecision`.

## Composición

`AllOfRequirement` exige que todos sus requisitos se cumplan.

`AnyOfRequirement` exige que al menos uno se cumpla.

Los contenedores anidados vacíos son inválidos para evitar configuraciones ambiguas.

El conjunto superior vacío es neutral y puede utilizarse como policy sin restricciones adicionales.

## RBAC policy

`RbacAuthorizationPolicy`:

1. resuelve roles y permisos efectivos;
2. evalúa los requisitos;
3. devuelve un resultado booleano de satisfacción.

El resultado todavía no es una decisión final del framework.

La adaptación de esta policy al decision engine existente de WP-227 se realizará sin introducir otro motor.

## Seguridad

Los requisitos:

- no autentican;
- no mutan principal;
- no modifican sesión;
- no elevan `AuthenticationLevel`;
- no consultan persistencia directamente.

## Criterios de aceptación

- Permission requirements.
- Role requirements.
- ALL/ANY composition.
- Fail-closed ante requisito faltante.
- Sin nuevo `AuthorizationDecision`.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
