---
id: EG-513
title: Shared Signals and Continuous Access Evaluation Architecture
summary: Define modelos y contratos para Security Event Tokens, Subject Identifiers, Shared Signals streams y Continuous Access Evaluation sin acoplar Foundation a transporte o criptografía concretos.
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
  - set
depends_on:
  - EG-512
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-513 — Shared Signals and Continuous Access Evaluation Architecture

## Objetivo

Introducir una arquitectura contract-first para intercambio de señales de seguridad y evaluación continua de acceso.

## Base conceptual

La arquitectura se apoya en:

- Security Event Token (SET);
- Subject Identifiers;
- Shared Signals Framework;
- Continuous Access Evaluation Profile;
- delivery adapters push/poll fuera de Foundation.

## Modelos

### SecurityEventSubject

Representa un Subject Identifier mediante formato y atributos tipados.

### SecurityEvent

Representa un hecho de seguridad con tipo, subject, instante y payload.

### SecurityEventToken

Representa un conjunto de eventos emitidos por un issuer.

### SharedSignalsStream

Representa una relación lógica entre issuer, audience y event types habilitados.

## Contratos

- `SecurityEventTokenVerifierInterface`;
- `SecurityEventPublisherInterface`;
- `SharedSignalsStreamRepositoryInterface`;
- `ContinuousAccessEvaluationHandlerInterface`.

## Neutralidad

Foundation no conoce HTTP push/poll, storage, Redis, JWT/JWS library, TLS termination ni proveedor IAM.

## Roadmap I1-I8

1. I1 — architecture, SET, subjects y stream contracts;
2. I2 — SET verification, issuer/audience/time/replay boundaries;
3. I3 — SSF stream configuration y delivery contracts;
4. I4 — CAEP session/access events;
5. I5 — RISC account-security events;
6. I6 — continuous session/token reaction policies;
7. I7 — SCIM/security-event interoperability y operational readiness;
8. I8 — product completion e integration tests.

## Criterios de aceptación

Modelos inmutables, contratos tipados, transport/crypto neutrality, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
