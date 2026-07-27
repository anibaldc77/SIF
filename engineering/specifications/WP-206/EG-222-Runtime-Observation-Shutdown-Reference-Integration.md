---
id: EG-222
title: Runtime Observation Shutdown Reference Integration
summary: Defines vertical opt-in reference scenarios for successful and failed shutdown observation without runtime interference.
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
  - shutdown
  - integration
depends_on:
  - EG-221
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

# EG-222 — Runtime Observation Shutdown Reference Integration

## 1. Purpose

This increment validates the complete opt-in lifecycle sequence from `run()` through `shutdown()` and proves that observation failures during shutdown cannot change the authoritative shutdown result or final runtime state.

## 2. Reference composition

The reference scenarios SHALL compose only public APIs:

1. `Framework::create()`;
2. `ListenerProvider`;
3. `EventDispatcher`;
4. `InMemoryObservationFailureReporter`;
5. isolated observation through `ObservationComposer`;
6. `ObservationLifecycleFacade`.

No observation component SHALL be registered automatically in `Application` or `Bootstrap`.

## 3. Successful shutdown scenario

The successful scenario SHALL:

- execute `run()` and then `shutdown()`;
- observe operations in the deterministic order `run`, `shutdown`;
- preserve the exact `BootResult` instances produced by the delegated Kernel;
- leave the Runtime in state `stopped`;
- produce no observation diagnostics.

## 4. Failed shutdown observation scenario

A listener SHALL fail only when observing `RuntimeOperation::Shutdown`. The failure SHALL:

- be isolated from the lifecycle call;
- produce one `OBSERVATION-001` diagnostic;
- preserve the original listener exception as the observation cause;
- preserve a successful shutdown `BootResult`;
- preserve the final runtime state `stopped`.

## 5. Executable examples

The repository SHALL include:

- `examples/runtime-observation-shutdown.php`;
- `examples/runtime-observation-shutdown-failure.php`.

Both examples MUST display the authoritative runtime result and final state. The failure example MUST also display the isolated observation diagnostic.

## 6. Compatibility boundary

This increment MUST NOT modify product runtime code, lifecycle transitions, default capabilities, application construction, or automatic event wiring.

## 7. Acceptance criteria

Acceptance requires focused and complete PHPUnit success, PHPStan level 8 with zero errors, Builder validation with zero diagnostics, deterministic governed artifact generation, and successful execution of both examples.
