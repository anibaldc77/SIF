---
id: EG-215
title: Observation Contracts and Runtime Characterization
summary: Defines the isolated observation contracts, immutable diagnostic model, and characterization evidence required before composing runtime lifecycle observation.
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
  - contracts
  - characterization
depends_on:
  - EG-214-A1
  - EG-213
  - WP-204
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-215 — Observation Contracts and Runtime Characterization

## 1. Purpose

This increment defines the side-effect-bounded observation boundary approved by EG-214-A1 without integrating that boundary into the default runtime composition graph.

## 2. Contracts

`EventObserverInterface` SHALL expose one operation:

```php
public function observe(object $event): ObservationResult;
```

`ObservationFailureReporterInterface` SHALL receive immutable `ObservationFailure` diagnostics. Reporting is observational and MUST NOT obtain lifecycle authority.

## 3. Result model

`ObservationResult` SHALL:

- retain the original event instance;
- distinguish success from isolated failure;
- expose at most one `ObservationFailure` for the current synchronous dispatcher semantics;
- never represent or reinterpret a runtime `BootResult`.

`ObservationFailure` SHALL retain:

- the observed event;
- the original listener exception;
- the occurrence timestamp;
- a serialization-safe diagnostic payload that excludes the event object and throwable object.

## 4. Isolation boundary

`IsolatedEventObserver` SHALL invoke the approved `EventDispatcherInterface` and capture every thrown `Throwable`.

A listener failure:

- MUST NOT escape `observe()`;
- MUST be returned as an `ObservationResult` failure;
- MAY be reported through `ObservationFailureReporterInterface`;
- MUST remain the primary observation cause even if the reporter also fails.

A reporter failure MUST NOT escape the isolation boundary.

## 5. Runtime non-interference

This increment SHALL NOT modify:

- `Application`;
- `Bootstrap`;
- `Kernel`;
- `Lifecycle`;
- `Runtime`;
- the state machine;
- historical capabilities.

Characterization tests SHALL prove that:

1. a failed observation does not change a successful application lifecycle;
2. a failed observation does not replace an existing runtime failure cause;
3. omitting observation preserves the approved baseline behavior.

## 6. Deferred scope

The following remain outside this increment:

- lifecycle adapter composition;
- automatic event construction;
- default bootstrap registration;
- asynchronous dispatch;
- persistent diagnostics;
- retries;
- listener health management.

## 7. Acceptance criteria

The increment is accepted when:

- all contracts and value objects pass unit tests;
- runtime characterization tests pass without production runtime changes;
- PHPStan level 8 reports zero errors;
- Builder validation reports zero diagnostics;
- governed artifact generation is deterministic.
