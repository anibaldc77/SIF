---
id: WP-238-ADOPTION-GUIDE
title: Guía de adopción de Identity Governance y Security Administration
summary: Describe cómo integrar WP-238 con Authorization, SCIM, Audit/Event Dispatcher y adapters productivos.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-08-08
updated: 2026-08-08
work_package: WP-238
tags:
  - security
  - governance
  - identity
  - adoption
depends_on:
  - EG-480
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Guía de adopción — WP-238

## Objetivo

Incorporar gobierno de identidades en una aplicación SIF sin acoplar Foundation a storage, UI, workflow engine o proveedores externos.

## Catálogo

Implementar `EntitlementCatalogInterface`.

El catálogo puede derivarse de permissions, roles, aplicaciones, recursos o capacidades externas.

## Assignments

Implementar `AccessAssignmentProviderInterface`.

Utilizar `EffectiveAccessAssignmentResolverInterface` para trabajar únicamente con accesos vigentes.

## Access Reviews

Implementar:

- `AccessReviewCampaignRepositoryInterface`;
- `AccessReviewerResolverInterface`;
- `AccessReviewWorkItemRepositoryInterface`.

El workflow concreto puede vivir en application layer.

## SoD y Risk

Implementar `SegregationOfDutiesRuleProviderInterface`.

Utilizar `GovernanceRiskEvaluatorInterface` para evaluación determinista.

## Exceptions

Implementar:

- `GovernanceExceptionRepositoryInterface`;
- `CompensatingControlRepositoryInterface`;
- `GovernanceExceptionApproverResolverInterface`.

Las excepciones deben tener expiración explícita y aprobación auditable.

## Remediation

Implementar:

- `GovernanceRemediationPlannerInterface`;
- `RemediationPlanRepositoryInterface`;
- `GovernanceExpirationProcessorInterface`.

La ejecución efectiva debe quedar en adapters/application services.

## Audit/Event

Implementar `GovernanceEventPublisherInterface` contra Event Dispatcher/Audit.

No agregar dependencia directa de storage de auditoría a Governance.

## Integración con Authorization

Las decisiones de governance pueden originar cambios de roles/permissions, pero Authorization continúa siendo responsable de runtime access decisions.

## Integración con SCIM

Las remediaciones pueden ejecutarse mediante SCIM cuando corresponda, pero Governance no debe conocer proveedores SCIM concretos.

## Seguridad mínima

- autorización administrativa;
- separación de reviewers y subjects cuando corresponda;
- audit trail;
- expiración de excepciones;
- validación de SoD;
- manejo seguro de evidencia;
- idempotencia de remediaciones;
- retries controlados;
- observabilidad.
