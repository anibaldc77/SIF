---
id: EG-519
title: SCIM Security Event Interoperability and Operational Readiness
summary: Define interoperabilidad entre señales de seguridad y provisioning, junto con readiness operacional para Shared Signals sin acoplar Foundation a SCIM adapters o infraestructura concretos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-10
updated: 2026-08-10
work_package: WP-243
tags:
  - security
  - shared-signals
  - scim
  - interoperability
  - readiness
depends_on:
  - EG-518
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-519 — SCIM Security Event Interoperability and Operational Readiness

## Objetivo

Relacionar señales de seguridad con cambios de provisioning/identidad y agregar readiness operacional verificable.

## Provisioning Context

`SecurityEventProvisioningContext` representa subject, operación y recurso lógico afectado.

## Interoperability Assessment

`SecurityEventInteroperabilityAssessment` expresa compatibilidad, violaciones y warnings.

## Operational Context

`SharedSignalsOperationalContext` representa capabilities disponibles y controles activos.

## Operational Readiness

`SharedSignalsOperationalReadinessReport` separa blocking issues de advisories.

## Contratos

- `SecurityEventProvisioningInteroperabilityPolicyInterface`;
- `SecurityEventProvisioningMapperInterface`;
- `SharedSignalsOperationalReadinessEvaluatorInterface`.

## Integración

Un adapter SCIM puede traducir eventos de provisioning a Shared Signals o mapear señales recibidas a contexto de provisioning.

Foundation no ejecuta operaciones SCIM concretas ni asume un Identity Provider específico.

## Seguridad

Implementaciones productivas deberán:

- validar subject mapping;
- impedir cambios de identidad cross-tenant;
- asegurar replay protection;
- auditar decisiones de provisioning;
- distinguir readiness interno de certificación externa.

## Neutralidad

Foundation no conoce SCIM HTTP client, base de datos, Redis, queue broker, IdP o provisioning engine concretos.

## Criterios de aceptación

Context/assessment/readiness tipados, mapper/policy/evaluator contracts, neutrality, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
