---
id: EG-450
title: Lifecycle coordinado de revocación, orden y semántica de fallas
summary: Define planificación determinística, ejecución ordenada, resultado parcial y comportamiento fail-closed para revocaciones federadas coordinadas.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-08-07
updated: 2026-08-07
work_package: WP-235
tags:
  - security
  - federation
  - revocation
  - lifecycle
depends_on:
  - EG-449
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-450 — Coordinated Revocation Lifecycle

## Objetivo

Incorporar una primera orquestación determinística de las operaciones de revocación definidas en I1.

## Orden para scope `all`

El orden obligatorio es:

1. sesiones locales;
2. credenciales del proveedor;
3. vínculo federado.

La desvinculación se ejecuta al final para preservar el contexto necesario mientras se revocan los otros alcances.

## Scope individual

Cuando el request solicita un único scope, sólo se ejecuta ese paso.

## Failure semantics

Cada paso produce `FederatedRevocationStepResult`.

Una excepción o rechazo explícito:

- marca el paso como fallido;
- detiene pasos destructivos posteriores;
- conserva los pasos ya completados;
- produce ejecución incompleta.

No se realiza rollback en I2.

## Execution

`FederatedRevocationExecution` conserva:

- request;
- pasos intentados;
- estado terminal;
- resultado agregado.

El resultado parcial es explícito y no se representa como éxito total.

## Eventos

El lifecycle publica:

- `federation.revocation.started`;
- `federation.revocation.step_succeeded`;
- `federation.revocation.step_failed`;
- `federation.revocation.completed`;
- `federation.revocation.incomplete`.

Los eventos no incluyen tokens ni secretos.

## Fuera de alcance de I2

I2 no implementa:

- retries;
- backoff;
- compensación;
- journaling persistente;
- idempotency keys;
- HTTP/CLI integration;
- adapters concretos de proveedores.

## Criterios de aceptación

- orden determinístico;
- scopes individuales aislados;
- failure detiene pasos posteriores;
- resultado parcial explícito;
- eventos terminales correctos;
- sin retries/rollback;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
