---
id: EG-204
title: Runtime Transition Engine
summary: Defines the stateless transition engine that centralizes the Runtime lifecycle graph while preserving Kernel authority and Runtime state ownership.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - runtime
  - state-machine
  - transition-engine
  - kernel
  - lifecycle
work_package: WP-201
depends_on:
  - EG-202
  - EG-203
  - WP-003-RUNTIME-FOUNDATION
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-204 — Runtime Transition Engine

## 1. Objective

WP-201-I2 extracts the lifecycle transition graph from `Runtime` into the stateless `RuntimeStateMachine` component.

The increment preserves the approved responsibility model:

- `Kernel` owns lifecycle command authority;
- `RuntimeStateMachine` owns transition rules and validation;
- `Runtime` owns current state, current stage, lifecycle timestamps, and failure cause;
- `Lifecycle` owns deterministic provider hook execution and result production.

## 2. Design constraints

`RuntimeStateMachine` SHALL:

- contain no mutable lifecycle state;
- expose the allowed destinations for a source state;
- determine whether a transition is valid;
- reject invalid transitions using `InvalidRuntimeTransitionException`;
- remain independent of `Application`, `Kernel`, providers, events, configuration, and external infrastructure.

It SHALL NOT become a second source of truth for Runtime state.

## 3. Approved graph

```text
Created       -> Bootstrapping | Failed
Bootstrapping -> Booted        | Failed
Booted        -> Running       | Stopping | Failed
Running       -> Stopping      | Failed
Stopping      -> Stopped       | Failed
Stopped       -> terminal
Failed        -> terminal
```

## 4. Compatibility

`Runtime` retains its existing public constructor behavior because the new dependency is optional. Existing calls to `new Runtime()` remain valid.

`RuntimeInterface::transitionTo()` and `RuntimeInterface::fail()` remain available as compatibility operations marked `@internal`. Their later removal requires an approved migration plan.

No Kernel, Lifecycle, Application, provider, or BootResult contract changes are authorized by this increment.

## 5. Acceptance criteria

- `Runtime` delegates transition validation to `RuntimeStateMachine`.
- The transition graph exists in one production component only.
- Every approved transition is covered by automated tests.
- Terminal states expose no outgoing transitions.
- Invalid transitions retain the established exception type and message.
- Existing Runtime lifecycle tests remain green.
- Composer validation succeeds.
- PHPUnit has no failures.
- PHPStan level 8 has no errors.
- Builder reports zero diagnostics after governed artifact regeneration.
