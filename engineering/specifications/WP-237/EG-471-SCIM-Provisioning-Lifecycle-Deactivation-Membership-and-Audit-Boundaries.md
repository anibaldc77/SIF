---
id: EG-471
title: Ciclo de vida SCIM, desactivación, consistencia de membresías y fronteras de auditoría
summary: Define lifecycle para Users y Groups, separación deactivate/delete, limpieza de membresías y publicación neutral de eventos.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors: [SIF Team]
created: 2026-08-08
updated: 2026-08-08
work_package: WP-237
tags: [security, scim, lifecycle, membership, audit]
depends_on: [EG-470]
related_adrs: [ADR-0005]
supersedes: null
superseded_by: null
---
# EG-471 — SCIM Provisioning Lifecycle

## Objetivo
Modelar el lifecycle SCIM sin ejecutar side effects.

## Reglas
- deactivate y delete son acciones distintas;
- User delete planifica deactivate, membership cleanup y delete por defecto;
- Group delete planifica membership cleanup y delete;
- `ScimLifecyclePolicy` permite desactivar pasos previos;
- `ScimProvisioningEventPublisherInterface` desacopla Audit/Event Dispatcher;
- `ScimMembershipConsistencyInterface` desacopla reconciliación de membresías.

## Fuera de alcance
Executor concreto, transacciones, adapters de audit/event, reconciliación periódica y provider-specific behavior.
