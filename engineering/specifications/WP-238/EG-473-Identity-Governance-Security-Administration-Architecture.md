---
id: EG-473
title: Arquitectura de Identity Governance y Security Administration
summary: Define entitlements, asignaciones, revisiones de acceso y contratos neutrales para gobierno de identidades y administración de seguridad.
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
  - identity
  - access-review
  - administration
depends_on:
  - EG-472
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-473 — Identity Governance and Security Administration Architecture

## Objetivo

Iniciar WP-238 con una foundation de gobierno de identidades y administración de seguridad desacoplada de storage, proveedor, UI y transporte.

## Entitlements

`Entitlement` representa una capacidad gobernable de acceso.

No reemplaza Permission/Role de autorización. Su función es aportar identidad estable, descripción, recurso y metadata de gobierno.

## Assignments

`AccessAssignment` vincula un subject con un entitlement y modela:

- fecha de asignación;
- expiración opcional;
- vigencia temporal.

La fuente concreta de assignments queda detrás de `AccessAssignmentProviderInterface`.

## Access reviews

`AccessReviewItem` separa el acceso actualmente asignado de la decisión de revisión.

`AccessReviewDecision` admite:

- approve;
- revoke;
- defer.

La publicación de la decisión queda detrás de `AccessReviewDecisionPublisherInterface`.

## Catalog

`EntitlementCatalogInterface` abstrae el catálogo de capacidades gobernables.

No prescribe base de datos, SCIM, LDAP ni proveedor federado.

## Relación con subsistemas existentes

WP-238 consume conceptualmente:

- Authorization para permisos/roles reales;
- SCIM para provisioning/deprovisioning;
- Audit/Event Dispatcher para trazabilidad;
- Identity Provider para sujetos externos.

No duplica ninguno de esos subsistemas.

## Fuera de alcance de I1

- campañas de revisión;
- SoD;
- approval workflows;
- risk scoring;
- automatic remediation;
- adapters de Authorization/SCIM;
- UI administrativa;
- persistencia.

## Criterios de aceptación

- entitlement tipado;
- assignment temporal;
- decisiones explícitas;
- review item separado;
- contracts neutrales;
- provider-neutral;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
