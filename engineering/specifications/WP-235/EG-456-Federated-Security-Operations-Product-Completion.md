---
id: EG-456
title: Cierre de producto de operaciones de seguridad federada
summary: Consolida revocación coordinada, journaling, idempotencia, retry, capabilities remotas y operaciones administrativas.
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
  - operations
  - product-completion
depends_on:
  - EG-455
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-456 — Federated Security Operations Product Completion

## Objetivo

Cerrar WP-235 como subsistema operacional para revocaciones federadas seguras, observables e idempotentes.

## Invariantes finales

- scope de revocación siempre explícito;
- orden coordinado: sesiones → proveedor → vínculo;
- falla detiene pasos posteriores;
- journal permanece persistence-neutral;
- operation id evita duplicados;
- ejecuciones completas se reutilizan;
- resume plan excluye pasos exitosos;
- retry policy es acotada;
- backoff es explícito y no ejecuta timers;
- capabilities del proveedor son declarativas;
- transient es retryable;
- permanent es terminal;
- unsupported nunca es éxito implícito;
- actions administrativas requieren confirmación;
- inspection es read-only;
- eventos y outputs no contienen secretos.

## Compatibilidad

La arquitectura puede integrarse con Keycloak, Microsoft Entra ID, Auth0, Okta y otros IdP mediante adapters externos.

Foundation no contiene dependencias específicas de proveedor.

## Fuera de alcance

WP-235 no implementa:

- scheduler;
- workers;
- HTTP client;
- persistence concreta;
- adapters concretos de IdP;
- secret storage;
- SCIM;
- administración de usuarios remotos.

## Criterios de aceptación

- ejecución administrativa end-to-end;
- idempotencia verificada;
- resume planning verificado;
- retry remoto transient;
- permanent terminal;
- provider/storage/scheduler neutral;
- suite completa sin errores;
- PHPStan limpio;
- Builder sin diagnósticos.
