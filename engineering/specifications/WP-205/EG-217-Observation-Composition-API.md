---
id: EG-217
title: Observation Composition API
summary: Defines explicit composition helpers, a null observer, and deterministic multiple-observer semantics for opt-in runtime observation.
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
  - composition
  - api
depends_on:
  - EG-216
  - EG-215
  - EG-214-A1
  - EG-213
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-217 — Observation Composition API

## 1. Purpose

This increment standardizes explicit host-side composition of the observation components approved by EG-215 and EG-216. It reduces integration ceremony without changing the default application graph.

## 2. Composition boundary

`ObservationComposer` SHALL expose explicit factories for:

- an isolated observer backed by an `EventDispatcherInterface`;
- zero, one, or multiple observers;
- an `ObservedKernel` around an existing `KernelInterface`.

The composer MUST NOT mutate an application, register a capability, replace a kernel, or retain global state.

## 3. Null observer

`NullEventObserver` SHALL accept every event and return a successful `ObservationResult` containing the same event instance. It SHALL perform no dispatch, reporting, logging, registration, or lifecycle work.

An empty observer composition SHALL resolve to `NullEventObserver`.

## 4. Multiple observers

`CompositeEventObserver` SHALL:

1. invoke observers in insertion order;
2. pass the exact same event instance to every observer;
3. continue after a returned failure;
4. defensively isolate a thrown observer exception;
5. retain the first observed failure as the aggregate result;
6. return success only when all observers succeed.

Later failures MUST NOT replace the first failure. Failure semantics affect only the observation result and MUST NOT acquire Runtime authority.

## 5. Cardinality rules

`ObservationComposer::combine()` SHALL use these deterministic rules:

- zero observers: create `NullEventObserver`;
- one observer: return the same observer instance;
- two or more observers: create `CompositeEventObserver` preserving argument order.

## 6. Runtime composition

`ObservationComposer::kernel()` SHALL return a new `ObservedKernel` decorating the exact supplied kernel and observer. It MUST NOT modify `Application`, `Bootstrap`, `Kernel`, `Lifecycle`, `Runtime`, or `RuntimeStateMachine`.

## 7. Acceptance criteria

The increment is accepted when:

- empty composition is a no-op;
- single composition preserves identity;
- multiple composition is ordered and deterministic;
- all observers are attempted after observation failures;
- thrown observer errors are isolated;
- first-failure semantics are verified;
- Runtime composition remains explicit and opt-in;
- PHPStan level 8 reports zero errors;
- Builder validation reports zero diagnostics;
- governed generation remains deterministic.
