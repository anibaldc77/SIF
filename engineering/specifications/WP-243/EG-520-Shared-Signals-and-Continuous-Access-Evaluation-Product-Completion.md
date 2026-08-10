---
id: EG-520
title: Shared Signals and Continuous Access Evaluation Product Completion
summary: Consolida Security Event Tokens, Shared Signals, CAEP, RISC, continuous access reactions y provisioning interoperability como superficie coherente de producto.
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
  - product-completion
depends_on:
  - EG-519
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-520 — Shared Signals and Continuous Access Evaluation Product Completion

## Objetivo

Cerrar WP-243 consolidando I1-I7 como una superficie coherente y verificable de Shared Signals y Continuous Access Evaluation.

## Capacidades consolidadas

`SharedSignalsProductCapabilities` representa:

- Security Event Tokens;
- Subject Identifiers;
- Stream Delivery;
- CAEP;
- RISC;
- Continuous Access Reactions;
- Provisioning Interoperability.

## Product Profile

`SharedSignalsProductProfile` expresa:

- nombre del perfil;
- capabilities;
- replay protection requerido;
- operational readiness requerido;
- continuous access reaction requerido.

## Readiness

`SharedSignalsProductReadinessReport` representa:

- readiness global;
- blocking issues;
- warnings.

`SharedSignalsProductReadinessEvaluatorInterface` define la frontera de evaluación.

## Cobertura acumulada WP-243

1. I1 — Shared Signals architecture;
2. I2 — SET verification y replay;
3. I3 — Stream configuration y delivery;
4. I4 — CAEP session/access events;
5. I5 — RISC account-security events;
6. I6 — Continuous session/token reactions;
7. I7 — SCIM interoperability y operational readiness;
8. I8 — Product Completion.

## Neutralidad

Foundation no prescribe JWT/JWS library, HTTP transport, queue broker, storage, Redis, session engine, token repository, SCIM adapter ni proveedor IAM.

## Criterios de aceptación

WP-243 se considera completo cuando PHPUnit, PHPStan, Composer y SIF Builder finalizan sin errores ni diagnósticos y `git diff --check` queda limpio.
