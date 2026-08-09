---
id: EG-478
title: Excepciones de gobierno, aceptación de riesgo y controles compensatorios
summary: Define excepciones temporales, aceptación explícita de riesgo, controles compensatorios y evaluación de vigencia.
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
  - exception
  - risk-acceptance
  - compensating-control
depends_on:
  - EG-477
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-478 — Governance Exceptions, Risk Acceptance and Compensating Controls

## Objetivo

Modelar excepciones temporales a reglas de gobierno y SoD con aceptación explícita de riesgo y controles compensatorios.

## Governance exception

`GovernanceException` representa:

- id;
- subject;
- conflict rule;
- status;
- requestedAt;
- expiresAt;
- risk acceptance opcional;
- compensating controls.

Status soportados:

- requested;
- approved;
- rejected;
- expired.

## Risk acceptance

`RiskAcceptance` registra:

- subject;
- nivel de riesgo aceptado;
- reason;
- acceptedAt;
- expiresAt.

Una aceptación vencida invalida la efectividad de la excepción.

## Compensating controls

`CompensatingControl` contiene:

- id;
- name;
- description;
- residual risk.

I6 no verifica ejecución material del control; sólo modela su declaración.

## Exception evaluator

`DefaultGovernanceExceptionEvaluator` considera efectiva una excepción cuando:

- está approved;
- está dentro de su ventana temporal;
- su risk acceptance, si existe, permanece vigente.

## Approval

`GovernanceExceptionDecision` mantiene outcome, approver, timestamp y reason.

`GovernanceExceptionApproverResolverInterface` desacopla la resolución del aprobador.

## Persistencia

- `GovernanceExceptionRepositoryInterface`;
- `CompensatingControlRepositoryInterface`.

Ambos permanecen storage-neutral.

## Separación de responsabilidades

I6 no:

- ejecuta remediation;
- deshabilita Authorization;
- modifica SCIM;
- agenda expiraciones;
- verifica controles externos;
- envía notificaciones.

## Fuera de alcance

- renewal workflow;
- automatic expiration jobs;
- evidence collection;
- control attestation;
- event/audit adapter;
- remediation after expiry.

## Criterios de aceptación

- approved exception efectiva en ventana;
- requested exception no efectiva;
- risk acceptance expirada invalida excepción;
- compensating control con residual risk;
- approval explícita;
- contracts neutrales;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
