---
id: EG-430
title: Cache de grants de autorización y diagnósticos determinísticos
summary: Define cache seguro de grants efectivos y diagnósticos sanitizados sin cachear decisiones contextuales ABAC.
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
  - cache
  - diagnostics
  - observability
depends_on:
  - EG-429
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-430 — Cache de grants y diagnósticos determinísticos

## Objetivo

Optimizar la resolución repetida de roles y permisos del principal sin reutilizar decisiones dependientes del recurso o del entorno.

## Cache permitido

Puede cachearse:

- `RoleSet`;
- `PermissionSet`;
- `ResolvedAuthorizationGrants`.

El cache se indexa por identidad y debe ofrecer invalidación explícita.

## Cache prohibido

WP-232 no cachea por defecto:

- `AuthorizationDecision`;
- resultado ABAC;
- atributos resource;
- atributos environment.

Una decisión sobre un recurso nunca debe reutilizarse automáticamente para otro recurso.

## Invalidation

La aplicación debe invalidar grants cuando cambien:

- roles;
- permisos directos;
- membresías;
- jerarquías relevantes.

La implementación in-memory es sólo una referencia.

## Diagnostics

Los diagnósticos exponen:

- allowed/denied;
- failure reason canónico;
- fingerprint de identidad;
- cantidad de roles;
- cantidad de permisos;
- fingerprint determinístico de evaluación.

No exponen valores de atributos ni identificadores en claro.

## Seguridad

Los diagnósticos no deben convertirse en un canal lateral de datos sensibles.

El cache no debe modificar la semántica de autorización.

## Criterios de aceptación

- Cache por identidad.
- Invalidación explícita.
- No cache de decisión ABAC.
- Diagnósticos sanitizados.
- Fingerprints determinísticos.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
