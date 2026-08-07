---
id: EG-452
title: Reanudación desde falla, política de retry y semántica de backoff
summary: Define resume planning para no repetir pasos exitosos y una política explícita de reintentos con backoff exponencial sin scheduler.
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
  - retry
  - backoff
depends_on:
  - EG-451
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-452 — Resume-from-Failure, Retry Policy y Backoff

## Objetivo

Evitar que un retry de una revocación incompleta vuelva a ejecutar pasos ya finalizados y modelar reintentos temporales de forma explícita.

## Resume plan

`FederatedRevocationResumePlanner` compara el plan solicitado con una ejecución previa.

Los pasos exitosos quedan excluidos.

Ejemplo:

- local sessions: success;
- provider credentials: failed;
- identity link: no ejecutado.

El resume plan contiene:

1. provider credentials;
2. identity link.

## Retry policy

`FederatedRevocationRetryPolicy` define:

- máximo de intentos;
- delay base;
- backoff exponencial determinístico.

I4 no introduce jitter porque la política permanece puramente determinística y testeable.

## Retry advisor

`FederatedRevocationRetryAdvisor` calcula:

- si queda un intento permitido;
- próxima fecha de elegibilidad;
- motivo de rechazo cuando se agotaron los intentos.

No espera ni ejecuta tareas.

## Scheduler boundary

Foundation no:

- llama sleep/usleep;
- crea cron jobs;
- programa timers;
- ejecuta retries automáticamente.

El caller o infraestructura externa decide cuándo volver a invocar.

## Seguridad

- pasos exitosos no se repiten;
- max attempts es acotado;
- next eligibility es explícito;
- agotamiento queda representado como decisión negativa;
- no existe loop automático de retry.

## Criterios de aceptación

- resume desde primer paso no completado;
- fresh execution conserva plan completo;
- backoff exponencial determinístico;
- next eligible at correcto;
- max attempts respetado;
- sin scheduler/sleep;
- PHPUnit focalizado sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
