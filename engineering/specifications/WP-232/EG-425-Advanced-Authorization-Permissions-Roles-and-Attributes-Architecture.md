---
id: EG-425
title: Arquitectura avanzada de autorización con permisos, roles y atributos
summary: Define el vocabulario y los límites de RBAC y ABAC sobre el motor de autorización existente de WP-227.
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
  - permissions
  - roles
  - abac
  - rbac
depends_on:
  - EG-424
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-425 — Arquitectura avanzada de autorización

## Contexto

WP-227 ya incorporó la arquitectura fundamental de autenticación, policies y decision engine.

WP-232 no crea un segundo motor de decisiones. Extiende el vocabulario disponible para que las policies existentes puedan utilizar permisos, roles y atributos contextuales.

## Principio

Permisos, roles y atributos son entradas de autorización.

No son una decisión.

La decisión final continúa perteneciendo al motor de autorización de WP-227.

## Permisos

Un permiso identifica una capacidad concreta, por ejemplo:

- `invoice.read`;
- `invoice.write`;
- `user.disable`.

Los identificadores son canónicos, normalizados y comparables.

`PermissionSet` representa una colección inmutable y determinística.

## Roles

Un rol agrupa semánticamente capacidades y podrá participar posteriormente en jerarquías.

Un rol no concede por sí mismo acceso hasta que una policy lo interprete.

`RoleSet` representa roles efectivos de un principal.

## Atributos

ABAC utiliza atributos contextuales tales como:

- tenant;
- departamento;
- ownership;
- clasificación;
- región;
- características del recurso.

Los atributos permanecen como datos escalares y no ejecutan lógica.

## Resolvers

Los contratos:

- `PermissionResolverInterface`;
- `RoleResolverInterface`;
- `AuthorizationAttributeProviderInterface`;

quedan desacoplados de persistencia y transporte.

Una aplicación puede obtener información desde BaseModel, SQL, LDAP, Keycloak, claims externos u otras fuentes mediante adaptadores.

## Separaciones obligatorias

WP-232 no debe:

- duplicar `AuthorizationDecision`;
- crear un segundo policy engine;
- autenticar identidades;
- modificar sesiones;
- elevar `AuthenticationLevel`;
- acoplar Core a una BD o proveedor externo.

## Evolución prevista

Las siguientes implementaciones podrán incorporar:

- role hierarchy;
- permission inheritance;
- policy requirements;
- RBAC/ABAC composition;
- caches;
- diagnostics;
- HTTP/CLI integration.

## Criterios de aceptación

- Vocabulario inmutable y canónico.
- Resolvers neutrales.
- Sin segundo motor de decisiones.
- PHPUnit focalizado sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
