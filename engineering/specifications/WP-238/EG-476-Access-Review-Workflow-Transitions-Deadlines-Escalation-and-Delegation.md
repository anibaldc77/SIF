---
id: EG-476
title: Transiciones de workflow, deadlines, escalación y delegación de revisiones de acceso
summary: Define transiciones válidas de work items, deadlines, delegación y escalación sin ejecutar remediaciones.
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
  - workflow
  - escalation
  - delegation
depends_on:
  - EG-475
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-476 — Access Review Workflow Transitions, Deadlines, Escalation and Delegation

## Objetivo

Formalizar el workflow de revisión sin acoplarlo a un workflow engine, notificaciones o remediación.

## Transiciones

`AccessReviewWorkflowManager` permite:

- pending -> in-review;
- in-review -> decided.

Transiciones fuera de estas reglas generan `InvalidAccessReviewWorkflowTransitionException`.

El manager produce `AccessReviewWorkflowTransition`; no muta ni persiste el work item.

## Deadline

`AccessReviewDeadline` modela un instante límite.

`overdueAt()` devuelve true cuando el instante consultado alcanza o supera el deadline.

## Delegation

`AccessReviewDelegation` representa reasignación explícita de reviewer con:

- reviewer origen;
- reviewer destino;
- timestamp;
- reason opcional.

## Escalation

`AccessReviewEscalation` representa escalación explícita con reason obligatoria.

`AccessReviewEscalationResolverInterface` permite resolver el reviewer destino sin prescribir jerarquías organizacionales.

## Separación de responsabilidades

I4:

- no envía emails;
- no ejecuta revocaciones;
- no modifica SCIM;
- no persiste automáticamente;
- no conoce un scheduler.

## Fuera de alcance

- timers;
- scheduled jobs;
- notification delivery;
- automatic escalation execution;
- delegation authorization rules;
- remediation.

## Criterios de aceptación

- transición pending -> in-review;
- transición in-review -> decided;
- invalid transitions rechazadas;
- deadline explícito;
- delegation explícita;
- escalation explícita;
- contratos neutrales;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
