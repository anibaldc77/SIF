---
id: WP-232-MIGRATION-GUIDE
title: Guía de adopción de autorización avanzada
summary: Describe la adopción gradual de permisos, roles, RBAC, ABAC, cache y diagnósticos de WP-232.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-232
tags:
  - security
  - authorization
  - rbac
  - abac
  - migration
depends_on:
  - EG-432
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Guía de adopción — WP-232

## Impacto

WP-232 es opt-in y aditivo sobre WP-227.

Las aplicaciones existentes pueden continuar utilizando el motor básico de policies sin adoptar RBAC/ABAC.

## Adopción RBAC

1. Implementar `RoleResolverInterface`.
2. Implementar `PermissionResolverInterface`.
3. Definir `RoleHierarchy`.
4. Construir `EffectivePermissionResolver`.
5. Construir `PrincipalAuthorizationGrantResolver`.
6. Declarar requisitos mediante `PermissionRequirement` y `RoleRequirement`.

## Adopción ABAC

1. Implementar `AuthorizationAttributeProviderInterface` para subject.
2. Construir atributos resource explícitamente en cada operación.
3. Construir atributos environment cuando sean relevantes.
4. Declarar `AttributeRequirement`.
5. Combinar requisitos mediante `ContextualRequirementSet`.

## Composición avanzada

Para exigir RBAC y ABAC:

1. crear `RbacAuthorizationPolicy`;
2. crear `AbacAuthorizationPolicy`;
3. combinarlas mediante `CompositeAuthorizationPolicy`;
4. utilizar `AdvancedAuthorizationService`.

El resultado final sigue siendo el `AuthorizationDecision` de WP-227.

## Cache

El cache de grants es opcional.

Debe invalidarse cuando cambien roles, permisos o membresías.

No se recomienda cachear decisiones ABAC salvo que la aplicación incluya correctamente todas las dimensiones contextuales en la clave.

## Diagnostics

Los diagnósticos incluidos son sanitizados.

Al integrarlos con logging, tracing o SIEM no deben añadirse valores de atributos sensibles sin una política explícita.

## Controller y HTTP

El bridge devuelve una decisión.

La aplicación decide si una denegación se representa como:

- 403;
- 404;
- excepción;
- error API;
- otra estrategia.

## Proveedores externos

Los contratos permiten adapters para Keycloak, LDAP, Active Directory u OIDC sin modificar WP-232.
