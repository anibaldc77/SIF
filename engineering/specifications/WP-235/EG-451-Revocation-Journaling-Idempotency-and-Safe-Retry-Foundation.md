---
id: EG-451
title: Journaling de revocación, idempotencia y base de retry seguro
summary: Define operation ids, journal transport-neutral, reutilización de ejecuciones completas y reintento explícito de ejecuciones incompletas.
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
  - journaling
  - idempotency
depends_on:
  - EG-450
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-451 — Revocation Journaling, Idempotency y Safe Retry

## Objetivo

Agregar identidad operacional y journal a las revocaciones coordinadas para evitar duplicados y permitir reintentos controlados.

## Operation ID

`FederatedRevocationOperationId` identifica una operación lógica de revocación.

El mismo operation id debe reutilizarse cuando un caller reintenta la misma operación.

## Journal

`FederatedRevocationJournalInterface` permite:

- buscar una ejecución previa;
- guardar el último record de una operación.

Foundation no define almacenamiento concreto.

## Execution record

`FederatedRevocationExecutionRecord` contiene:

- operation id;
- execution;
- recordedAt;
- estado completed derivado.

## Idempotency decision

`FederatedRevocationIdempotencyGuard` produce:

- `execute`: operación nueva;
- `reuse_completed`: ejecución ya finalizada;
- `retry_incomplete`: ejecución previa incompleta.

## Coordinator

`FederatedRevocationCoordinator`:

1. consulta el guard;
2. reutiliza ejecuciones completas sin repetir efectos;
3. ejecuta operaciones nuevas;
4. permite reejecutar operaciones incompletas;
5. guarda el nuevo execution record.

## Seguridad operacional

- una ejecución completa no se repite;
- una ejecución incompleta no se presenta como completa;
- no existe scheduler automático;
- no existe backoff;
- no existe sleep;
- el journal permanece persistence-neutral.

## Limitación deliberada

En I3 un retry de una operación incompleta vuelve a ejecutar el lifecycle completo solicitado.

La optimización de reanudar desde el primer paso incompleto queda para un incremento posterior.

## Criterios de aceptación

- operation id validado;
- completed reuse sin efectos duplicados;
- incomplete elegible para retry;
- journal conserva latest execution;
- persistence-neutral;
- sin scheduler/backoff;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
