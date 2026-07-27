---
id: EG-220
title: Runtime Observation Reference Integration
summary: Defines the first vertical opt-in integration example for the runtime observation subsystem without modifying the default application graph.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-24
updated: 2026-07-24
work_package: WP-206
tags:
  - runtime
  - events
  - observation
  - integration
  - example
depends_on:
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

# EG-220 — Runtime Observation Reference Integration

## 1. Purpose

This increment validates the complete observation stack in one representative runtime flow while preserving explicit opt-in composition.

## 2. Reference composition

The reference integration SHALL compose:

1. `Framework::create()`;
2. `ListenerProvider`;
3. `EventDispatcher`;
4. `InMemoryObservationFailureReporter`;
5. `IsolatedEventObserver` through `ObservationComposer`;
6. `ObservationLifecycleFacade`.

No component SHALL be automatically registered in `Application` or `Bootstrap`.

## 3. Runtime authority

The decorated Kernel remains the sole authority for lifecycle transitions and `BootResult` creation. The reference integration MUST return the exact result produced by the Kernel.

## 4. Failure isolation

A listener failure SHALL:

- produce a failed `ObservationResult`;
- produce one `OBSERVATION-001` diagnostic;
- leave a successful Runtime result successful;
- leave Runtime state unchanged by the observation failure.

## 5. Example

The repository SHALL include an executable example under `examples/runtime-observation.php`. The example MUST use only public APIs and MUST NOT require an external logging dependency.

## 6. Compatibility boundary

This increment MUST NOT modify:

- `Application`;
- `Bootstrap`;
- `Kernel`;
- `Lifecycle`;
- `Runtime`;
- `RuntimeStateMachine`;
- capability registration.
