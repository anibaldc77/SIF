---
id: EG-216
title: Explicit Runtime Observation Adapter
summary: Defines an opt-in Kernel decorator that observes completed runtime operations without changing runtime authority, results, failure causes, or default composition.
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
  - adapter
  - kernel
depends_on:
  - EG-215
  - EG-214-A1
  - EG-213
  - WP-204
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-216 — Explicit Runtime Observation Adapter

## 1. Purpose

This increment introduces the first explicit composition point between the approved Runtime and the isolated observation boundary. The integration is opt-in and remains outside the default application graph.

## 2. Adapter model

`ObservedKernel` SHALL implement `KernelInterface` and decorate another `KernelInterface`.

For each operation it SHALL:

1. invoke the delegate;
2. receive the authoritative `BootResult`;
3. construct one immutable `RuntimeOperationCompleted` event;
4. attempt observation;
5. return the exact `BootResult` instance produced by the delegate.

## 3. Runtime authority

The delegate Kernel and Runtime remain authoritative.

The adapter MUST NOT:

- perform state transitions;
- reinterpret success or failure;
- create replacement `BootResult` objects;
- replace a failure cause;
- register itself in Bootstrap;
- add a capability;
- modify `Application`, `Bootstrap`, `Kernel`, `Lifecycle`, `Runtime`, or `RuntimeStateMachine`.

## 4. Observation isolation

Observation occurs only after a `BootResult` exists. An observer exception MUST be captured by the adapter and MUST NOT alter the returned result or runtime state.

If the delegate throws before returning a result:

- the original throwable MUST escape unchanged;
- no completed-operation event SHALL be emitted;
- the adapter MUST NOT fabricate a result.

## 5. Event model

`RuntimeOperationCompleted` SHALL retain:

- the application;
- the exact delegated `BootResult`;
- the operation (`boot`, `run`, or `shutdown`);
- the observation timestamp.

Its serialized form MUST exclude the application object, throwable objects, and error object graphs.

## 6. Composition

Composition remains manual:

```php
$observedKernel = new ObservedKernel($application->kernel(), $observer);
$result = $observedKernel->run($application);
```

The existing `Application` continues using its original Kernel unless the caller explicitly invokes the adapter.

## 7. Acceptance criteria

The increment is accepted when:

- successful results are returned by identity;
- failed results retain their original cause;
- observer exceptions do not escape;
- delegate exceptions escape unchanged;
- no observation occurs without a delegated result;
- PHPStan level 8 reports zero errors;
- Builder validation reports zero diagnostics;
- governed generation remains deterministic.
