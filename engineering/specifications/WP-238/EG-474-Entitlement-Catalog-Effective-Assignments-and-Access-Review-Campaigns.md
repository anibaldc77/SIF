---
id: EG-474
title: Catálogo de entitlements, asignaciones efectivas y campañas de revisión de acceso
summary: Define resolución de accesos vigentes y campañas de revisión con alcance, período y estado explícitos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-238
tags:
  - security
  - governance
  - entitlements
  - access-review
  - campaign
depends_on:
  - EG-473
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-474 — Entitlement Catalog, Effective Assignments and Access Review Campaigns

## Objetivo

Agregar resolución determinista de asignaciones efectivas y el modelo de campañas de revisión sin ejecutar autorización ni remediación.

## Effective assignments

`DefaultEffectiveAccessAssignmentResolver` combina:

- `AccessAssignmentProviderInterface`;
- `EntitlementCatalogInterface`.

Sólo devuelve assignments:

- vigentes en el instante consultado;
- cuyo entitlement existe en el catálogo.

Un assignment desconocido no se transforma automáticamente en entitlement.

## Campaigns

`AccessReviewCampaign` define:

- id;
- name;
- status;
- scope;
- startsAt;
- endsAt.

Status soportados:

- draft;
- active;
- closed.

Una campaña sólo está activa cuando status y ventana temporal son válidos.

## Scope

`AccessReviewScope` permite seleccionar:

- subjects;
- entitlements;
- ambos.

Un scope vacío puede representar una campaña cuyo alcance será resuelto por una policy externa posterior.

## Persistencia

`AccessReviewCampaignRepositoryInterface` abstrae la persistencia de campañas.

## Separación de responsabilidades

El resolver:

- no decide autorización;
- no modifica SCIM;
- no revoca permisos;
- no publica auditoría.

La campaña:

- no ejecuta decisiones;
- no envía notificaciones;
- no realiza remediación.

## Fuera de alcance de I2

- generación automática de review items;
- reviewers;
- campañas recurrentes;
- segregation of duties;
- risk scoring;
- remediation;
- escalations.

## Criterios de aceptación

- resolution filtra expirados;
- resolution ignora entitlements inexistentes;
- campaign con status/período/scope explícitos;
- repository neutral;
- separación de Authorization/SCIM;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
