---
id: EG-518
title: Continuous Session and Token Reaction Policies
summary: Define decisiones y ejecución contractual de reacciones continuas sobre sesiones y tokens a partir de evaluaciones CAEP/RISC.
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
  - caep
  - risc
  - continuous-access
depends_on:
  - EG-517
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-518 — Continuous Session and Token Reaction Policies

## Objetivo

Transformar evaluaciones CAEP/RISC en decisiones operativas explícitas sin acoplar Foundation a mecanismos concretos de sesión o token storage.

## Action Model

`ContinuousAccessAction` representa:

- none;
- reevaluate;
- require reauthentication;
- revoke session;
- revoke tokens;
- disable access.

## Context

`ContinuousAccessContext` identifica subject y targets opcionales:

- session id;
- token id;
- client id.

Debe existir al menos un target operativo.

## Decision

`ContinuousAccessDecision` expresa acciones y razones.

## Execution Result

`ContinuousAccessExecutionResult` separa acciones completadas de fallidas.

## Contratos

- `ContinuousAccessDecisionPolicyInterface`;
- `ContinuousAccessReactionExecutorInterface`;
- `SessionRevocationServiceInterface`;
- `TokenRevocationServiceInterface`;
- `ReauthenticationRequirementServiceInterface`.

## Integración

La decision policy puede combinar resultados CAEP y RISC.

El executor coordina adapters concretos de sesiones, OAuth tokens, autenticación y autorización.

## Seguridad

Implementaciones productivas deberán:

- aplicar decisiones idempotentes;
- evitar revocaciones cruzadas entre subjects;
- auditar acciones sin exponer secretos;
- tratar fallas parciales explícitamente;
- preservar trazabilidad entre señal, decisión y acción.

## Neutralidad

Foundation no conoce session storage, token repository, Redis, HTTP middleware ni persistencia concreta.

## Criterios de aceptación

Actions/context/decision/result tipados, decision/executor contracts, servicios de revocación abstractos, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
