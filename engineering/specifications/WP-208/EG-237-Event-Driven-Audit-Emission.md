---
id: EG-237
title: Event-Driven Audit Emission
summary: Defines immutable audit events, explicit emitter contracts, Event Dispatcher integration, null emission, and failure boundaries without persistence coupling.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-208
tags:
  - foundation
  - audit
  - events
  - dispatcher
  - emission
depends_on:
  - EG-236
  - EG-213
  - EG-233
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-237 — Event-Driven Audit Emission

## Purpose

This specification defines explicit event-driven emission for immutable Audit records.

The increment does not implement persistence, repositories, queues, database adapters, static facades, model hooks, retention policies, or automatic Runtime integration.

## Public contracts

### AuditEmitterInterface

`AuditEmitterInterface` accepts an `AuditRecordInterface`, emits it through an implementation-specific mechanism, and returns the same authoritative record.

The emitter does not create, serialize, redact, or mutate records.

### AuditEventInterface

`AuditEventInterface` exposes the immutable audit record carried by an audit event.

## AuditRecordCreated

`AuditRecordCreated` is the initial immutable Audit event.

It:

- carries exactly one `AuditRecordInterface`;
- preserves record identity;
- adds no transport metadata;
- performs no serialization;
- performs no persistence;
- does not implement stoppable propagation.

## EventDispatcherAuditEmitter

`EventDispatcherAuditEmitter` integrates Audit with the existing Event Dispatcher.

It:

- creates `AuditRecordCreated`;
- dispatches it synchronously;
- returns the original record instance;
- uses existing listener ordering and priority rules;
- propagates listener exceptions according to the dispatcher contract;
- performs no I/O or persistence.

## NullAuditEmitter

`NullAuditEmitter` is an explicit no-op emitter.

It returns the original record and performs no dispatch.

It is suitable for tests, disabled audit configurations, and explicit composition without nullable dependencies.

## Failure policy

This increment does not introduce hidden error isolation.

Listener exceptions propagate according to the current Event Dispatcher behavior.

Future composition layers may introduce explicit isolation or diagnostics, but they must not mutate or replace the original record.

## Storage neutrality

Listeners may later:

- persist records;
- forward them to queues;
- write them to files;
- send them to external services;
- collect them in memory.

Those listeners are adapters outside the Audit Core.

## Acceptance criteria

- immutable audit event exists;
- explicit emitter contract exists;
- Event Dispatcher integration is opt-in;
- original record identity is preserved;
- listener ordering follows dispatcher rules;
- listener failure behavior is explicit;
- null emission is supported;
- no persistence dependency is introduced;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
