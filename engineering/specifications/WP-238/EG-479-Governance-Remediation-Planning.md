---
id: EG-479
title: Planificación de remediación de gobierno, procesamiento de expiraciones y fronteras de auditoría/eventos
summary: Define contratos neutrales para planificación de remediación, expiración de excepciones y publicación de eventos de gobierno.
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
  - remediation
  - expiration
  - audit
  - events
depends_on:
  - EG-478
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-479 — Governance Remediation Planning, Expiration Processing and Audit/Event Boundaries

## Objetivo

Definir las fronteras arquitectónicas necesarias para planificar remediaciones de gobierno, procesar expiraciones y publicar eventos, sin acoplar Foundation a storage, scheduler, transporte ni proveedores concretos.

## Remediation planning

`GovernanceRemediationPlannerInterface` representa la frontera para construir planes de remediación a partir de decisiones de gobierno ya adoptadas.

La planificación no ejecuta cambios sobre Authorization, SCIM, directorios ni aplicaciones.

## Remediation plan persistence

`RemediationPlanRepositoryInterface` abstrae la persistencia de planes de remediación.

Foundation no prescribe base de datos, cola, filesystem ni proveedor externo.

## Expiration processing

`GovernanceExpirationProcessorInterface` representa la frontera encargada de evaluar y procesar expiraciones de artefactos de gobierno.

Su ejecución concreta puede ser invocada por scheduler, CLI, worker u otro mecanismo externo, pero ninguno de ellos forma parte de I7.

## Audit/Event boundary

`GovernanceEventPublisherInterface` representa la frontera de publicación de eventos hacia Audit/Event Dispatcher u otros adapters.

El contrato no conoce storage de auditoría ni transporte.

## Separación de responsabilidades

I7 no:

- revoca accesos directamente;
- modifica SCIM;
- ejecuta Authorization;
- agenda tareas;
- envía notificaciones;
- persiste auditoría;
- conoce proveedores externos.

## Fuera de alcance

- ejecución concreta de planes;
- scheduler;
- retries;
- compensating rollback;
- adapters de Audit/Event Dispatcher;
- reconciliación;
- UI administrativa.

## Criterios de aceptación

- contratos de remediation explícitos;
- frontera de expiration processing;
- frontera de event publication;
- storage-neutral;
- provider-neutral;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
