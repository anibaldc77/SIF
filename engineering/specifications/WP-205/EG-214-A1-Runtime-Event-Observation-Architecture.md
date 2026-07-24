---
id: EG-214-A1
title: Runtime Event Observation Architecture
summary: Defines a non-invasive opt-in adapter for observing approved runtime lifecycle events without changing lifecycle authority, results, transitions, or default composition.
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
  - compatibility
depends_on:
  - EG-213
  - WP-204
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-214-A1 — Runtime Event Observation Architecture

## 1. Decision

SIF SHALL integrate runtime event observation through an explicit, opt-in adapter outside the default `Application`, `Bootstrap`, `Kernel`, `Lifecycle`, and `Runtime` composition graph.

The approved runtime remains the sole authority for state transitions, provider execution, boot results, shutdown results, and failure causes. Event observation SHALL be side-effect bounded and SHALL NOT determine, replace, cancel, retry, or reinterpret lifecycle execution.

This specification replaces the rejected embedded-dispatch approach conceptually. No production integration is authorized until this specification is approved.

## 2. Architectural rule

The integration SHALL follow this direction only:

```text
Approved Runtime Operation
        |
        +----> authoritative lifecycle result
        |
        +----> observation adapter ----> EventDispatcher ----> listeners
```

The reverse dependency is forbidden. A listener SHALL NOT become a lifecycle controller.

## 3. Composition boundary

The implementation SHALL introduce a separate adapter or decorator that wraps an approved public runtime boundary. The adapter:

- MUST be created explicitly by the host application;
- MUST NOT be registered automatically by `Bootstrap`;
- MUST NOT alter the default object graph;
- MUST NOT add a historical application capability;
- MUST NOT require changes to existing public lifecycle contracts unless a separately approved compatibility analysis authorizes them;
- MUST be removable without changing runtime behavior.

The detailed design SHALL choose one approved public boundary, preferring composition over modification. Direct edits to `Application`, `Bootstrap`, `Kernel`, `Lifecycle`, or `Runtime` are outside WP-205-I2 unless separately approved.

## 4. Observation semantics

### 4.1 Runtime authority

The adapter SHALL delegate to the approved runtime operation exactly once. The authoritative return value, state transition, warnings, errors, and cause SHALL be those produced by the wrapped runtime object.

### 4.2 Listener failures

The core `EventDispatcher` defined by EG-213 propagates listener exceptions unchanged. The observation adapter SHALL therefore establish an isolation boundary around dispatch.

A listener exception:

- SHALL NOT prevent the wrapped runtime operation from executing;
- SHALL NOT change a successful runtime result into a failure result;
- SHALL NOT replace an existing runtime failure cause;
- SHALL NOT trigger a second lifecycle transition;
- SHALL be captured as observation diagnostics available to the host;
- MAY be forwarded to a separately supplied diagnostic sink that itself obeys the same isolation rule.

The adapter SHALL catch only failures originating in observation. It SHALL NOT swallow exceptions originating in the wrapped runtime operation.

### 4.3 Before and after observations

When a lifecycle operation has both pre-operation and post-operation events:

1. the pre-operation event is offered to observers;
2. observation failures are recorded and isolated;
3. the authoritative runtime operation executes exactly once;
4. the post-operation event is offered only when its existing semantic preconditions are satisfied;
5. the authoritative result is returned unchanged.

A stoppable event stops only further listener invocation for that event. It SHALL NOT stop, cancel, or roll back the runtime operation.

### 4.4 Runtime failures

When the wrapped runtime reports or throws an approved lifecycle failure, the adapter MAY observe `FrameworkFailed` according to the existing event DTO semantics. Any listener failure while observing that failure SHALL be recorded separately and SHALL NOT replace the original runtime cause.

## 5. Event model

WP-205-I2 SHALL reuse the immutable event DTOs already approved by the Runtime Foundation. It SHALL NOT introduce duplicate lifecycle event types merely to simplify adapter implementation.

Event construction SHALL use existing public factories, constructors, values, and invariants. Tests SHALL NOT bypass private constructors or instantiate domain objects through unsupported paths.

## 6. Diagnostic model

The detailed design SHALL define an immutable observation result or diagnostic collection containing, at minimum:

- event type;
- listener or dispatch context when available without reflection;
- original throwable;
- deterministic sequence number;
- lifecycle phase associated with the observation.

Diagnostics SHALL be additive observability data. They SHALL NOT be inserted into existing `BootResult` or runtime failure collections unless a later specification explicitly changes those contracts.

## 7. Determinism

For identical runtime input and listener registration order:

- lifecycle behavior SHALL remain identical with or without the observation adapter;
- listener order SHALL remain that defined by EG-213;
- diagnostic ordering SHALL be deterministic;
- the adapter SHALL perform no filesystem scanning, reflection, attribute discovery, or global registration.

## 8. Compatibility invariants

The implementation SHALL prove all of the following:

1. the existing 467-test WP-205-I1 baseline remains green;
2. constructing the default runtime graph performs no dispatch;
3. running the default runtime graph performs no dispatch;
4. enabling observation does not change successful lifecycle results;
5. listener exceptions do not change successful lifecycle results;
6. listener exceptions do not replace original runtime failure causes;
7. provider registration, boot, and shutdown order remains unchanged;
8. capability lists remain unchanged;
9. runtime state transitions remain unchanged;
10. disabling or removing the adapter restores the exact default behavior.

## 9. Required verification strategy

Before implementation, WP-205-I2 SHALL add characterization tests for the existing runtime behavior. Implementation tests SHALL then cover:

- successful boot and shutdown with no listeners;
- successful boot and shutdown with recording listeners;
- listener failure before an operation;
- listener failure after an operation;
- existing runtime failure plus listener failure;
- deterministic diagnostic order;
- stoppable listener propagation without lifecycle cancellation;
- independent adapter instances with no shared mutable state;
- complete baseline regression suite;
- PHPStan level 8;
- governed Builder validation and deterministic second generation.

## 10. Explicit non-goals

WP-205-I2 SHALL NOT implement:

- asynchronous dispatch;
- queues, transports, or retries;
- event sourcing or event persistence;
- automatic discovery;
- global dispatcher state;
- lifecycle cancellation by listeners;
- mutation of runtime results by listeners;
- default registration in `Bootstrap`;
- changes to the state machine;
- new historical capabilities;
- HTTP, Console, Module Registry, Context, or Audit integration.

## 11. Implementation increments

After approval, implementation SHALL proceed in separate reviewable increments:

### I2-B1 — Characterization and contracts

- baseline characterization tests;
- observation diagnostic contracts;
- no runtime integration.

### I2-B2 — Isolated observation adapter

- explicit adapter/decorator;
- listener-failure isolation;
- focused tests;
- no default composition changes.

### I2-B3 — Lifecycle event mapping

- mapping to approved immutable DTOs;
- complete compatibility tests;
- documentation and governed artifacts.

Each increment SHALL independently pass all quality gates before the next begins.

## 12. Acceptance criteria

This specification is implementation-ready only when the Architecture Board approves:

- the exact wrapped public boundary;
- the immutable diagnostic contract;
- the listener-failure isolation policy;
- the event-to-lifecycle mapping table;
- proof that no default runtime class requires modification;
- the characterization test matrix.
