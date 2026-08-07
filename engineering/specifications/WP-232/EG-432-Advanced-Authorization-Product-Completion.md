---
id: EG-432
title: Cierre de producto de autorización avanzada
summary: Define los invariantes finales, criterios end-to-end y límites de producto de WP-232.
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
  - abac
  - product-completion
depends_on:
  - EG-431
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-432 — Cierre de producto de autorización avanzada

## Objetivo

Cerrar WP-232 validando la composición integral de permisos, roles, jerarquías, ABAC, cache de grants, diagnósticos e integración.

## Invariantes finales

- WP-227 conserva el único `AuthorizationDecision`.
- Los roles y permisos son datos, no decisiones.
- La herencia de roles es transitiva y detecta ciclos.
- Roles desconocidos no conceden permisos implícitos.
- Los atributos ABAC se separan en subject, resource y environment.
- Atributos faltantes fallan cerrado.
- No existe lenguaje de expresiones arbitrarias.
- RBAC y ABAC se combinan de forma conjunctiva por defecto.
- El cache sólo almacena grants efectivos.
- Las decisiones contextuales ABAC no se cachean por defecto.
- Los diagnósticos no exponen valores sensibles.
- La integración Controller/HTTP devuelve la decisión canónica y no impone 403/404.
- La autorización no modifica autenticación ni `AuthenticationLevel`.

## Compatibilidad

WP-232 es aditivo sobre WP-227.

No exige cambiar aplicaciones que utilicen exclusivamente las policies básicas previas.

## Persistencia

Los resolvers permanecen detrás de contratos.

La aplicación puede resolver roles, permisos y atributos desde:

- BaseModel;
- SQL;
- LDAP;
- Active Directory;
- Keycloak;
- claims OIDC;
- servicios externos.

WP-232 no depende de ninguno de ellos.

## Criterios de aceptación

- End-to-end RBAC + ABAC allow.
- Fail-closed ABAC.
- Herencia de roles y permisos efectiva.
- Cache sin reutilización de decisión contextual.
- Diagnósticos sanitizados.
- Controller bridge con decisión canónica.
- Suite completa sin errores.
- PHPStan limpio.
- Builder sin diagnósticos.
