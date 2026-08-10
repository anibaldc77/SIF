---
id: EG-517
title: RISC Account Security Events
summary: Define modelos, policies y reacciones para eventos RISC de seguridad de cuenta sin acoplar Foundation a almacenamiento de cuentas o mecanismos concretos de revocación.
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
  - risc
  - account-security
depends_on:
  - EG-516
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# EG-517 — RISC Account Security Events

## Objetivo

Agregar eventos RISC orientados a cambios de seguridad de cuenta y compromisos que requieran una reacción coordinada.

## Tipos de evento

`RiscEventType` representa inicialmente:

- account disabled;
- account enabled;
- credential compromise;
- credential change;
- identifier change;
- recovery activated;
- recovery information changed.

## Modelo

`RiscAccountSecurityEvent` contiene type, subject, occurredAt y details.

## Reacciones

`RiscAccountReaction` permite expresar:

- none;
- flag account;
- require reauthentication;
- revoke sessions;
- revoke tokens;
- disable access.

## Evaluación

`RiscEvaluationResult` separa reacción y razones.

## Contratos

- `RiscEventPolicyInterface`;
- `RiscAccountReactionHandlerInterface`;
- `RiscSecurityEventMapperInterface`.

## Integración

Los handlers concretos pueden integrarse con cuentas, sesiones, MFA, recuperación y OAuth mediante adapters.

## Neutralidad

Foundation no conoce repositorios de usuario, session storage, token storage, Redis, HTTP middleware ni proveedor IAM.

## Criterios de aceptación

Tipos/eventos/reacciones tipados, policy y mapper contracts, neutralidad de implementación, PHPUnit/PHPStan limpios y Builder sin diagnósticos.
