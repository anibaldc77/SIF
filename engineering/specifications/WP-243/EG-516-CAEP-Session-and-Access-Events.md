---
id: EG-516
title: CAEP Session and Access Events
summary: Define modelos y policies para eventos CAEP que afecten sesiones y acceso continuo sin acoplar Foundation a mecanismos concretos de sesión o revocación de tokens.
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
  - sessions
depends_on:
  - EG-515
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-516 — CAEP Session and Access Events

## Objetivo

Agregar eventos CAEP orientados a cambios que requieran reevaluar sesiones o acceso continuo.

## Tipos de evento

`CaepEventType` representa inicialmente:

- session revoked;
- token claims change;
- assurance level change;
- device compliance change.

## Modelo

`CaepSessionEvent` contiene type, subject, occurredAt y details.

## Reacciones

`CaepAccessReaction` permite expresar:

- none;
- reauthenticate;
- reevaluate;
- revoke session;
- revoke tokens.

## Evaluación

`CaepEvaluationResult` separa reacción y razones.

## Contratos

- `CaepEventPolicyInterface`;
- `CaepSessionReactionHandlerInterface`;
- `CaepSecurityEventMapperInterface`.

## Integración

El mapper traduce Security Events verificados a eventos CAEP tipados.

El reaction handler se integra posteriormente con subsistemas de sesión, autenticación y OAuth mediante adapters.

## Neutralidad

Foundation no conoce `session_destroy`, session storage, token repository, HTTP middleware o Redis.

## Criterios de aceptación

Tipos/eventos/reacciones tipados, policy y mapper contracts, session/token neutrality, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
