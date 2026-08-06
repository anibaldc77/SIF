---
id: EG-390
title: Políticas de autorización y motor de decisiones
summary: Define contratos neutrales, registro determinista y evaluación fail-closed para autorización extensible.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-06
updated: 2026-08-06
work_package: WP-227
tags:
  - security
  - authorization
  - policy
  - decision
depends_on:
  - EG-385
  - EG-386
  - EG-389
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-390 — Políticas de autorización y motor de decisiones

## 1. Objetivo

Establecer un núcleo de autorización desacoplado de HTTP, persistencia, roles concretos y proveedores externos, capaz de soportar futuras estrategias RBAC, ABAC, ownership y multi-tenant.

## 2. Modelo

- `AuthorizationAction` representa una operación estable y normalizada.
- `AuthorizationResource` representa tipo, identificador opcional y atributos escalares.
- `AuthorizationContext` transporta atributos de evaluación request-scoped.
- `AuthorizationRequest` combina principal, acción, recurso y contexto.
- `AuthorizationDecision` expresa únicamente allow o deny con motivo seguro.

## 3. Políticas y registro

`AuthorizationPolicyInterface` separa aplicabilidad y decisión. El registro conserva orden de incorporación, rechaza identificadores duplicados y permite múltiples políticas aplicables.

## 4. Algoritmo de decisión

El motor aplica una estrategia all-must-allow:

1. si ninguna política aplica, deniega;
2. si una política falla técnicamente, deniega;
3. si una política deniega, prevalece la denegación;
4. solamente permite cuando todas las políticas aplicables permiten.

## 5. Invariantes

- La evaluación siempre falla cerrada.
- Las excepciones técnicas no se propagan como detalle público.
- Los motivos de denegación son estables y no sensibles.
- El núcleo no conoce roles, permisos persistidos, tablas, HTTP ni Keycloak.
- Los atributos usan valores escalares y orden canónico.

## 6. Extensibilidad

Las políticas futuras podrán interpretar atributos del principal, recurso y contexto para implementar RBAC, ABAC, ownership, tenant isolation o integración externa sin modificar el motor central.
