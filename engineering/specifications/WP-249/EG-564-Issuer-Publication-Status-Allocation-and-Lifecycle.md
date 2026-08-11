---
id: EG-564
title: Issuer Publication Status Allocation and Lifecycle
summary: Define issuer-side credential status allocation, lifecycle transitions, publication planning and publishing boundaries without coupling Foundation to storage, HTTP, locking or scheduling implementations.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-11
updated: 2026-08-11
work_package: WP-249
tags:
  - security
  - verifiable-credentials
  - credential-status
  - issuer
  - publication
  - lifecycle
depends_on:
  - EG-563
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-564 — Issuer Publication, Status Allocation and Lifecycle

## Objetivo

Modelar la responsabilidad del issuer para asignar posiciones de status, gestionar transiciones de lifecycle y publicar nuevas versiones de status lists.

## Lifecycle

`CredentialStatusLifecycleState` distingue:

- valid;
- suspended;
- revoked.

`CredentialStatusLifecycleTransition` representa origen, destino, instante efectivo y razón.

## Allocation

`CredentialStatusAllocation` representa credential id, status-list id, index y purpose.

La estrategia concreta de allocation permanece detrás de `CredentialStatusAllocationRepositoryInterface`.

## Publication

`CredentialStatusPublicationPlan` representa:

- status-list id;
- mechanism;
- version;
- publish timestamp;
- allocations involucradas.

`CredentialStatusPublicationResult` expresa resultado, versión publicada, timestamp y warnings.

## Contratos

- `CredentialStatusAllocationRepositoryInterface`;
- `CredentialStatusLifecyclePolicyInterface`;
- `CredentialStatusPublicationPlannerInterface`;
- `CredentialStatusPublisherInterface`.

## Seguridad y concurrencia

La implementación productiva deberá impedir asignaciones duplicadas, race conditions, rollback silencioso de versiones y transición inválida de revoked a estados recuperables.

La coordinación transaccional y locking son responsabilidades de adapters concretos.

## Neutralidad

Foundation no conoce SQL, Redis, distributed locks, HTTP, scheduler, cron, filesystem o queue concreta.

## Compatibilidad

I4 no modifica contracts públicos anteriores. Amplía WP-249 mediante tipos issuer-side nuevos.

## Criterios de aceptación

Lifecycle/allocation/publication tipados, contracts separados, neutralidad de infraestructura, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
