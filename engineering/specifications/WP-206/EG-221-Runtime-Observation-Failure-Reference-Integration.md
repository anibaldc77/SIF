---
id: EG-221
title: Runtime Observation Failure Reference Integration
summary: Defines a vertical opt-in reference scenario proving that listener failures are diagnosed without changing runtime results or state.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-206
tags:
  - runtime
  - events
  - observation
  - diagnostics
  - integration
depends_on:
  - EG-220
  - EG-219
  - EG-218
  - EG-217
  - EG-216
  - EG-215
  - EG-214-A1
  - EG-213
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-221 — Runtime Observation Failure Reference Integration

## 1. Purpose

This increment validates the failure path of the complete observation stack in a representative runtime flow while preserving explicit opt-in composition and runtime authority.

## 2. Reference composition

The reference failure integration SHALL compose only public APIs:

1. `Framework::create()`;
2. `ListenerProvider` with one intentionally failing listener;
3. `EventDispatcher`;
4. `InMemoryObservationFailureReporter`;
5. isolated observation through `ObservationComposer`;
6. `ObservationLifecycleFacade`.

No component SHALL be registered automatically in `Application` or `Bootstrap`.

## 3. Runtime authority

The decorated Kernel remains the sole authority for lifecycle transitions and `BootResult` creation. A listener failure MUST NOT change a successful runtime result into a failure and MUST NOT change the resulting runtime state.

## 4. Diagnostic contract

A listener failure SHALL produce exactly one stable diagnostic with:

- code `OBSERVATION-001`;
- the observed event type;
- the original cause type;
- the original cause message;
- an ISO-8601 occurrence timestamp.

The diagnostic representation MUST NOT contain stack traces, host paths, object dumps, or mutable runtime state.

## 5. Example

The repository SHALL include an executable example under `examples/runtime-observation-failure.php`. The example MUST demonstrate both the successful runtime result and the failed observation result.

## 6. Compatibility boundary

This increment MUST NOT modify:

- `Application`;
- `Bootstrap`;
- `Kernel`;
- `Lifecycle`;
- `Runtime`;
- `RuntimeStateMachine`;
- capability registration.
