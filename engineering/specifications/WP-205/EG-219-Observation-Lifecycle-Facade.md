---
id: EG-219
title: Observation Lifecycle Facade
summary: Defines an explicit lifecycle facade that composes the approved observed Kernel and exposes the latest isolated observation result without changing Runtime authority.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-24
updated: 2026-07-24
work_package: WP-205
tags:
  - runtime
  - events
  - observation
  - facade
depends_on:
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

# EG-219 — Observation Lifecycle Facade

## 1. Purpose

This increment provides a small explicit API for observed `boot()`, `run()`, and `shutdown()` operations while preserving the Kernel as the sole lifecycle authority.

## 2. Composition model

`ObservationLifecycleFacade` SHALL compose:

1. an existing `KernelInterface`;
2. an explicit `EventObserverInterface`;
3. `LatestObservationRecorder`;
4. the approved `ObservedKernel` decorator.

The facade MUST NOT replace the Kernel stored by `Application` and MUST NOT be composed automatically by `Bootstrap`.

## 3. Authoritative result

Every lifecycle method SHALL return the exact `BootResult` instance returned by the decorated Kernel. Observation state MUST NOT alter, wrap, reinterpret, or replace that result.

## 4. Latest observation

The facade SHALL expose:

- whether an observation result exists;
- the latest `ObservationResult`, or `null` before observation;
- explicit clearing of facade-local observation state.

Clearing observation state MUST NOT alter Runtime state, prior events, diagnostics, or the Kernel result.

## 5. Failure isolation

`LatestObservationRecorder` SHALL convert an exception thrown directly by a non-conforming observer into an `ObservationFailure`. Such an exception MUST NOT escape into the lifecycle caller.

A failed observation result SHALL remain inspectable while the authoritative `BootResult` remains unchanged.

## 6. Explicit boundary

The facade is opt-in infrastructure. This increment MUST NOT modify:

- `Application`;
- `Bootstrap`;
- `Kernel`;
- `Lifecycle`;
- `Runtime`;
- `RuntimeStateMachine`;
- capability registration.

## 7. Exclusions

This increment does not provide automatic discovery, global state, persistence, asynchronous dispatch, retries, or host logging integration.
