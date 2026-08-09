---
id: EG-475
title: Generación de ítems de revisión, asignación de reviewers y estado de workflow
summary: Define work items de revisión de acceso, resolución de reviewers y estados de workflow sin ejecutar remediaciones.
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
  - access-review
  - reviewer
  - workflow
depends_on:
  - EG-474
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-475 — Access Review Item Generation, Reviewer Assignment and Workflow State

## Objetivo

Generar work items de revisión desde campañas y assignments efectivos, asignando reviewer mediante contrato y sin ejecutar remediaciones.

## Reviewer

`AccessReviewerId` representa al reviewer asignado.

`AccessReviewerResolverInterface` decide qué reviewer corresponde a una combinación campaign + assignment.

I3 no prescribe manager hierarchy, owner, group approver ni workflow engine.

## Work item

`AccessReviewWorkItem` contiene:

- campaign id;
- effective assignment;
- reviewer;
- workflow status;
- decision opcional.

Workflow status:

- pending;
- in-review;
- decided.

La decisión sigue representada por `AccessReviewDecision`.

## Generator

`AccessReviewWorkItemGenerator`:

1. exige campaign activa en el instante consultado;
2. obtiene assignments efectivos;
3. aplica scope de campaign;
4. crea work items pending.

Scope vacío se interpreta como sin restricción adicional.

## Persistencia

`AccessReviewWorkItemRepositoryInterface` abstrae almacenamiento de work items.

## Seguridad y separación

El generator:

- no revoca accesos;
- no modifica SCIM;
- no persiste automáticamente;
- no publica decisiones;
- no decide Authorization.

## Fuera de alcance de I3

- transiciones command-based;
- deadlines;
- escalations;
- delegación;
- notifications;
- remediation;
- SoD;
- risk scoring.

## Criterios de aceptación

- generación sólo para campaign activa;
- filtering por scope;
- reviewer explícito;
- status pending;
- decision separada de status;
- contracts neutrales;
- sin remediation;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
