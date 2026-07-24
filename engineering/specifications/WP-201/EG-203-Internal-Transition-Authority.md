---
id: EG-203
title: Internal Transition Authority
summary: Defines the compatibility-preserving first implementation increment that centralizes Runtime lifecycle transitions in Kernel.
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
  - kernel
  - lifecycle
  - state-machine
  - compatibility
work_package: WP-201
depends_on:
  - EG-202
  - WP-003-RUNTIME-FOUNDATION
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-203 — Internal Transition Authority

## 1. Objective

WP-201-I1A centralizes all operational Runtime state changes performed by SIF Foundation in `Kernel`.

`Lifecycle` executes provider hooks in deterministic order and returns a `BootResult`. It does not mutate Runtime state.

## 2. Compatibility boundary

The methods `RuntimeInterface::transitionTo()` and `RuntimeInterface::fail()` remain available during this increment. They are marked as infrastructure-level compatibility operations with `@internal`.

Their removal or replacement requires a separately approved compatibility plan. WP-201-I1A does not authorize that breaking change.

## 3. Kernel authority

Kernel applies these transitions:

```text
boot:
Created -> Bootstrapping -> Booted | Failed

run:
Created -> Bootstrapping -> Booted -> Running | Failed
Booted -> Running | Failed

shutdown:
Booted -> Stopping -> Stopped | Failed
Running -> Stopping -> Stopped | Failed
```

Kernel also converts exceptions raised during command orchestration into a failed `BootResult` and, where legal, transitions Runtime to `Failed`.

## 4. Lifecycle boundary

Lifecycle is responsible only for:

- provider registration in insertion order;
- provider boot in insertion order;
- provider shutdown in reverse order;
- collecting shutdown errors;
- preserving the first shutdown cause;
- returning structured results.

Lifecycle must not invoke `transitionTo()` or `fail()`.

## 5. State graph refinement

WP-201-I1A adds the transition:

```text
Booted -> Stopping
```

This permits clean shutdown after explicit `boot()` without requiring `run()`.

Terminal states remain immutable.

## 6. Acceptance criteria

- Kernel is the only production class in SIF Foundation that invokes Runtime transition mutators.
- Lifecycle returns results without changing Runtime state.
- `Application::boot()` followed by `Application::shutdown()` succeeds.
- Existing public signatures remain available.
- Composer validation succeeds.
- PHPUnit has no failures.
- PHPStan level 8 has no errors.
- Builder reports zero diagnostics after governed artifact regeneration.
