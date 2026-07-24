---
id: WP-201-ARCHITECTURE-REVIEW
title: WP-201 Runtime Core Model Architecture Review
summary: Reviews EG-202 for responsibility separation, WP-003 compatibility, lifecycle completeness, and compliance with the capability-driven Runtime architecture.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - runtime
  - architecture-review
  - lifecycle
  - compatibility
work_package: WP-201
depends_on:
  - EG-202
related_adrs:
  - ADR-0004
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-201 — Runtime Core Model Architecture Review

## 1. Review objective

Evaluate whether EG-202 provides an implementable and compatibility-aware Runtime Core model without introducing responsibilities assigned to later Runtime Work Packages.

## 2. Materials reviewed

- WP-003 Runtime Foundation Specification;
- EG-200 SIF Runtime Architecture;
- EG-201 Capability Model;
- ADR-0005 Capability-Driven Runtime;
- current WP-003 production contracts and lifecycle implementation;
- EG-202 Runtime Core Model.

## 3. Findings

### 3.1 Responsibility separation

**Result:** Conformant.

EG-202 separates the observable Runtime record from Kernel lifecycle authority and Lifecycle orchestration. It keeps adapter loops, service resolution, modules, configuration, and infrastructure services outside Runtime Core.

### 3.2 Compatibility with WP-003

**Result:** Conformant with explicit refinements.

The model retains the WP-003 public state vocabulary and Kernel commands. It identifies, rather than silently changes, areas that require implementation review:

- public transition mutators;
- shutdown from `Booted`;
- repeated command behavior;
- deterministic time;
- Runtime Context;
- atomic transition authority.

No immediate breaking signature change is authorized by EG-202 alone.

### 3.3 Capability-driven architecture

**Result:** Conformant.

EG-202 acknowledges the capability boundary but does not implement or duplicate the Capability Registry. Runtime Core remains independent of concrete infrastructure providers.

### 3.4 State completeness

**Result:** Conformant.

The legal transition graph covers startup, active execution, clean shutdown before or after run, terminal failure, and illegal transitions. Distinguishing BootStage from RuntimeState avoids a second overlapping state machine.

### 3.5 Error model

**Result:** Conformant.

The specification preserves fail-fast startup, best-effort shutdown, structured diagnostics, and first-cause retention. Detailed final-state policy for shutdown errors is intentionally left for an implementation increment and must be resolved before code changes.

### 3.6 Testability

**Result:** Conformant.

The specification recognizes direct wall-clock construction as a testability boundary and requires an internal injectable time source without prematurely defining the public clock capability.

## 4. Non-blocking design questions

The following questions SHALL be decided in WP-201 implementation specifications:

1. Whether invalid Kernel commands throw a typed exception or return a failed `BootResult` consistently.
2. Whether shutdown errors produce terminal `Stopped` with failed result, or terminal `Failed` after all shutdown attempts.
3. How transition mutation is hidden from consumers while maintaining compatibility with `RuntimeInterface`.
4. Whether Runtime Context is always present as an empty immutable object or is nullable during initial composition.
5. The exact contract name and visibility of the internal time boundary.

These are not blockers for approving the conceptual model, but no production implementation SHALL guess them silently.

## 5. Risks

| Risk | Impact | Mitigation |
|---|---|---|
| Breaking existing RuntimeInterface consumers | High | Add compatibility tests and migration analysis before changing public methods. |
| Runtime becoming a service locator | High | Keep arbitrary service resolution outside EG-202 and enforce ADR-0005. |
| Duplicate state semantics | Medium | Treat BootStage only as diagnostic lifecycle context. |
| Inconsistent invalid-command behavior | Medium | Resolve once in WP-201-I2 and apply to every Kernel command. |
| Time abstraction becoming infrastructure | Low | Keep it internal and dependency-free until the clock capability is designed. |

## 6. Decision

**Recommendation:** Approve EG-202 for implementation planning, subject to explicit resolution of the five non-blocking design questions in the relevant increments.

## 7. Entry criteria for WP-201-I1

WP-201-I1 may begin only after:

- EG-202 status is changed to `Approved`;
- this review status is changed to `Approved`;
- the invalid-command policy for transition operations is selected;
- a compatibility baseline test list is recorded;
- Builder validation reports zero diagnostics.
