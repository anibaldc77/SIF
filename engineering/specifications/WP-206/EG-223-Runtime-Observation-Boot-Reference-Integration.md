---
id: EG-223
title: Runtime Observation Boot Reference Integration
summary: Defines vertical opt-in reference scenarios for successful and failed boot observation without runtime interference.
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
  - boot
  - integration
depends_on:
  - EG-222
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

# EG-223 — Runtime Observation Boot Reference Integration

## 1. Purpose

This increment validates explicit observation of `boot()` and proves that listener failures during boot observation cannot change the authoritative boot result or runtime state.

## 2. Reference composition

The reference scenarios SHALL compose only public APIs: `Framework::create()`, `ListenerProvider`, `EventDispatcher`, `InMemoryObservationFailureReporter`, isolated observation through `ObservationComposer`, and `ObservationLifecycleFacade`.

No observation component SHALL be registered automatically in `Application` or `Bootstrap`.

## 3. Successful boot scenario

The successful scenario SHALL:

- execute `boot()` through the explicit facade;
- observe exactly one `boot` operation;
- preserve the exact `BootResult` instance produced by the delegated Kernel;
- leave the Runtime in state `booted`;
- produce no observation diagnostics.

## 4. Failed boot observation scenario

A listener SHALL fail only when observing `RuntimeOperation::Boot`. The failure SHALL:

- be isolated from the lifecycle call;
- produce one `OBSERVATION-001` diagnostic;
- preserve the original listener exception as the observation cause;
- preserve a successful boot `BootResult`;
- preserve the Runtime state `booted`.

## 5. Executable examples

The repository SHALL include successful and failed boot-observation examples. Both MUST display the authoritative boot result and runtime state. The failure example MUST display the isolated diagnostic.

## 6. Compatibility boundary

This increment MUST NOT modify product runtime code, lifecycle transitions, default capabilities, application construction, or automatic event wiring.

## 7. Acceptance criteria

Acceptance requires focused and complete PHPUnit success, PHPStan level 8 with zero errors, Builder validation with zero diagnostics, deterministic governed artifact generation, and successful execution of both examples.
