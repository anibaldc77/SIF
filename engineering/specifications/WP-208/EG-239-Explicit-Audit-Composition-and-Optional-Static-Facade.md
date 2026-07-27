---
id: EG-239
title: Explicit Audit Composition and Optional Static Facade
summary: Defines instance-based audit orchestration and an optional static convenience facade that delegates to explicit services without storing execution context.
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
  - composition
  - facade
depends_on:
  - EG-238
  - EG-237
  - EG-236
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-239 — Explicit Audit Composition and Optional Static Facade

## Purpose

This specification defines the instance-based Audit orchestration API and an optional static convenience facade.

The instance service is the architectural source of truth. The static facade is not required for Audit usage.

## AuditServiceInterface

`AuditServiceInterface` combines:

- record construction;
- explicit emission.

Every call requires an `ExecutionContextInterface`.

The service does not infer, store, or retrieve ambient context.

## AuditService

`AuditService` composes:

- `AuditRecordFactoryInterface`;
- `AuditEmitterInterface`.

The service:

1. creates an immutable record;
2. emits that record;
3. returns the authoritative record.

It performs no persistence directly.

## Optional Audit facade

`Audit` is a static convenience entry point.

It:

- delegates to a configured `AuditServiceInterface`;
- stores only the configured service reference;
- stores no Execution Context;
- requires context on every record call;
- fails with `AuditNotConfiguredException` when unconfigured;
- supports explicit reset;
- supports explicit reconfiguration;
- is not the only supported API.

Applications that require strict dependency injection should use `AuditServiceInterface` directly.

## Global-state boundary

The facade configuration is process-local mutable state and must be treated as an integration convenience.

The facade must not:

- store current user or actor;
- store current context;
- create hidden scopes;
- infer request information;
- own persistence;
- become a dependency of lower-level Core contracts.

Tests using the facade must reset it after use.

## Failure behavior

Factory and emitter failures propagate according to their existing contracts.

The facade does not swallow or reinterpret errors.

## Exclusions

This increment does not implement:

- database adapters;
- retention;
- async delivery;
- automatic Runtime wiring;
- automatic BaseModel hooks;
- global context;
- service container registration;
- application bootstrap changes.

## Acceptance criteria

- instance-based orchestration exists;
- record creation and emission are composed explicitly;
- the facade delegates to an instance service;
- context is required per call;
- unconfigured usage fails predictably;
- reset and reconfiguration are explicit;
- no persistence or Runtime coupling is introduced;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
