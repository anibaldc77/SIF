# WP-004 — Runtime Orchestration

**Document ID:** WP-004-06

**Work Package:** WP-004 — Runtime Composition Engine

**Version:** 0.4.0

**Status:** Draft for Review

**Category:** Normative Specification

**Author:** SIF Architecture Board

---

# Executive Summary

This specification defines the Runtime Orchestration Model of the SIF Runtime Composition Engine.

The Runtime Orchestration Model coordinates the complete lifecycle of the Runtime Composition Engine from initialization through shutdown.

It defines:

* Runtime lifecycle;
* bootstrap sequence;
* Service Provider orchestration;
* module orchestration;
* registration window;
* runtime state transitions;
* container finalization;
* shutdown behavior.

This document is normative.

Every implementation of the Runtime Composition Engine SHALL conform to this specification.

---

# 1. Purpose

The purpose of this specification is to define the deterministic orchestration of the Runtime Composition Engine.

Runtime Orchestration coordinates the interaction between Registration, Resolution and Application startup.

The orchestration process SHALL guarantee that every Runtime reaches a consistent operational state before dependency resolution becomes available.

---

# 2. Scope

This specification defines:

* Runtime bootstrap;
* Runtime lifecycle;
* Runtime state transitions;
* Service Provider execution;
* module orchestration;
* registration phase;
* Runtime finalization;
* shutdown sequence.

This specification does **not** define:

* Binding registration;
* dependency resolution;
* dependency graph traversal;
* Binding validation.

Those responsibilities are specified by:

* WP-004-04 — Binding and Registration Model
* WP-004-05 — Resolution Engine

---

# 3. Relationship with the Runtime Composition Engine

The Runtime Composition Engine is composed of three complementary subsystems.

| Part | Specification                  | Responsibility                 |
| ---- | ------------------------------ | ------------------------------ |
| I    | Binding and Registration Model | Composition metadata           |
| II   | Resolution Engine              | Runtime object resolution      |
| III  | Runtime Orchestration          | Runtime lifecycle coordination |

This document specifies Part III.

---

# 4. Runtime Concepts

## 4.1 Runtime

The Runtime represents one executable composition environment.

A Runtime owns exactly one Container Aggregate.

---

## 4.2 Bootstrap

Bootstrap is the process that transforms an uninitialized Runtime into an operational Runtime.

Bootstrap SHALL execute exactly once.

---

## 4.3 Registration Window

The Registration Window is the period during which Bindings may be registered, replaced or removed.

Outside the Registration Window, Registration Operations SHALL be prohibited.

---

## 4.4 Frozen Runtime

A Frozen Runtime accepts Resolution Requests but rejects every Registration Operation.

The Frozen state guarantees that the composition graph remains immutable during execution.

---

## 4.5 Shutdown

Shutdown is the controlled termination of the Runtime.

Shutdown SHALL release Runtime-owned resources in a deterministic order.

---

# 5. Runtime Lifecycle

Every Runtime SHALL progress through the following conceptual lifecycle.

```text
Created
    │
    ▼
Bootstrapping
    │
    ▼
Registering
    │
    ▼
Frozen
    │
    ▼
Running
    │
    ▼
Shutting Down
    │
    ▼
Disposed
```

Each Runtime state SHALL complete successfully before transitioning to the next state.

---

# 6. Runtime Design Goals

The Runtime Orchestration Model pursues the following objectives:

* deterministic startup;
* deterministic shutdown;
* immutable runtime after bootstrap;
* explicit orchestration;
* reproducible execution;
* module independence;
* predictable operational state.

---

# 7. Architectural Principles

The Runtime SHALL satisfy the following principles.

1. Bootstrap executes exactly once.
2. Registration precedes Resolution.
3. Resolution begins only after Runtime finalization.
4. Runtime state transitions are deterministic.
5. Frozen Runtime prohibits Registration.
6. Running Runtime permits Resolution.
7. Shutdown completes deterministically.

---

# 8. Runtime State Model

The Runtime SHALL exist in exactly one Runtime State.

Only operations explicitly permitted by the active Runtime State may execute.

Illegal operations SHALL fail using typed Runtime exceptions.

---

# 9. Runtime Invariants

The Runtime SHALL preserve the following invariants.

| ID       | Invariant                                                   |
| -------- | ----------------------------------------------------------- |
| INV-RT01 | A Runtime owns exactly one Container Aggregate.             |
| INV-RT02 | Bootstrap executes exactly once.                            |
| INV-RT03 | Registration completes before Resolution becomes available. |
| INV-RT04 | Frozen Runtime prohibits Registration Operations.           |
| INV-RT05 | Running Runtime permits Resolution Requests only.           |
| INV-RT06 | Runtime state transitions are deterministic.                |
| INV-RT07 | Shutdown preserves Runtime consistency.                     |
| INV-RT08 | Disposed Runtime accepts no further operations.             |

---

# 10. Bootstrap Process

## 10.1 Overview

Bootstrap is the deterministic process that initializes the Runtime Composition Engine.

Bootstrap SHALL execute exactly once during the lifetime of a Runtime.

A Bootstrap process SHALL either complete successfully or terminate the Runtime initialization with a typed exception.

---

## 10.2 Bootstrap Stages

The Bootstrap process SHALL execute the following stages in order:

1. Runtime creation
2. Runtime initialization
3. Service Provider discovery
4. Service Provider registration
5. Module registration
6. Binding validation
7. Runtime finalization
8. Runtime freeze
9. Runtime ready

No implementation MAY alter the observable order of these stages.

---

## 10.3 Bootstrap Completion

Bootstrap completes when the Runtime reaches the **Frozen** state.

Only after successful completion MAY Resolution Requests be accepted.

---

# 11. Service Provider Orchestration

## 11.1 Responsibilities

A Service Provider participates in Runtime initialization.

A Service Provider MAY:

* register Bindings;
* register Aliases;
* register configuration metadata;
* participate in module initialization.

A Service Provider SHALL NOT resolve runtime services during registration unless explicitly permitted by a future specification.

---

## 11.2 Execution Order

Service Providers SHALL execute in deterministic order.

Equivalent Runtime configurations SHALL execute Service Providers in the same observable sequence.

---

## 11.3 Failure Handling

Failure of a mandatory Service Provider SHALL terminate Bootstrap.

The Runtime SHALL NOT transition to the Frozen state after a failed Bootstrap.

---

# 12. Module Orchestration

## 12.1 Overview

Modules participate in Runtime initialization through the Runtime Orchestration Model.

Module initialization SHALL occur during the Registration Window.

---

## 12.2 Registration Order

Module registration SHALL be deterministic.

Module dependencies SHALL be satisfied before dependent modules execute.

---

## 12.3 Module Isolation

Module initialization SHALL NOT modify already finalized Runtime state.

Modules SHALL interact only through documented Runtime contracts.

---

# 13. Runtime Finalization

## 13.1 Purpose

Runtime Finalization completes Bootstrap and prepares the Runtime for execution.

---

## 13.2 Finalization Responsibilities

Runtime Finalization SHALL:

* verify Runtime consistency;
* complete registration validation;
* invalidate temporary bootstrap state;
* prepare shared runtime structures;
* freeze Registration.

---

## 13.3 Frozen Runtime

After finalization:

* Registration Operations SHALL be rejected.
* Resolution Requests SHALL become available.
* Runtime metadata SHALL remain immutable.

---

# 14. Runtime Rules

## RT-001 — Bootstrap Once

**Statement**

Bootstrap SHALL execute exactly once.

**Rationale**

Guarantees deterministic Runtime initialization.

---

## RT-002 — Deterministic Bootstrap

**Statement**

Equivalent Runtime configurations SHALL produce equivalent Bootstrap sequences.

---

## RT-003 — Registration Window

**Statement**

Registration Operations SHALL be permitted only during the Registration Window.

---

## RT-004 — Freeze Before Resolution

**Statement**

Resolution SHALL become available only after Runtime Finalization completes.

---

## RT-005 — Frozen Runtime

**Statement**

A Frozen Runtime SHALL reject Registration Operations.

---

## RT-006 — Deterministic Provider Execution

**Statement**

Service Providers SHALL execute in deterministic order.

---

## RT-007 — Module Dependency Order

**Statement**

Dependent Modules SHALL execute only after all required Modules have completed initialization.

---

## RT-008 — Finalization Consistency

**Statement**

Runtime Finalization SHALL verify Runtime consistency before freezing the Container.

---

## RT-009 — Runtime Isolation

**Statement**

One Runtime SHALL NOT affect the state of another Runtime.

---

## RT-010 — Bootstrap Failure

**Statement**

A failed Bootstrap SHALL prevent the Runtime from entering the Running state.

---

# 15. Runtime State Machine

## 15.1 Overview

The Runtime SHALL exist in exactly one Runtime State at any point in time.

Each Runtime State defines the operations that are permitted while the Runtime remains in that state.

---

## 15.2 Runtime States

| State         | Description                                               |
| ------------- | --------------------------------------------------------- |
| Created       | Runtime object exists but initialization has not started. |
| Bootstrapping | Runtime initialization is in progress.                    |
| Registering   | Registration Window is active.                            |
| Frozen        | Registration is closed and the Runtime is finalized.      |
| Running       | Runtime accepts Resolution Requests.                      |
| Shutting Down | Runtime termination is in progress.                       |
| Disposed      | Runtime has released all owned resources.                 |

---

## 15.3 State Transitions

```text
Created
    │
    ▼
Bootstrapping
    │
    ▼
Registering
    │
    ▼
Frozen
    │
    ▼
Running
    │
    ▼
Shutting Down
    │
    ▼
Disposed
```

No implementation SHALL permit any transition not defined by this specification.

---

## 15.4 Permitted Operations

| State         | Register | Resolve | Replace | Remove |
| ------------- | :------: | :-----: | :-----: | :----: |
| Created       |     ❌    |    ❌    |    ❌    |    ❌   |
| Bootstrapping |     ✅    |    ❌    |    ✅    |    ✅   |
| Registering   |     ✅    |    ❌    |    ✅    |    ✅   |
| Frozen        |     ❌    |    ✅    |    ❌    |    ❌   |
| Running       |     ❌    |    ✅    |    ❌    |    ❌   |
| Shutting Down |     ❌    |    ❌    |    ❌    |    ❌   |
| Disposed      |     ❌    |    ❌    |    ❌    |    ❌   |

Illegal operations SHALL fail using typed Runtime exceptions.

---

# 16. Runtime Events

## 16.1 Overview

The Runtime MAY publish lifecycle events.

Lifecycle events SHALL NOT modify Runtime state.

---

## 16.2 Standard Events

| Event                 | Description                   |
| --------------------- | ----------------------------- |
| RuntimeCreated        | Runtime object created.       |
| BootstrapStarted      | Bootstrap begins.             |
| RegistrationStarted   | Registration Window opens.    |
| RegistrationCompleted | Registration Window closes.   |
| RuntimeFrozen         | Runtime enters Frozen state.  |
| RuntimeStarted        | Runtime enters Running state. |
| ShutdownStarted       | Shutdown begins.              |
| RuntimeDisposed       | Runtime resources released.   |

---

## 16.3 Event Ordering

Lifecycle events SHALL be emitted in deterministic order.

Equivalent Runtime executions SHALL emit equivalent event sequences.

---

# 17. Scope Lifecycle

## 17.1 Purpose

Scopes define bounded execution contexts for Scoped Lifetime Policies.

---

## 17.2 Scope Creation

A Scope SHALL be created only by Runtime-managed mechanisms.

Every Scope SHALL belong to exactly one Runtime.

---

## 17.3 Scope Disposal

Disposing a Scope SHALL release every Runtime-owned Scoped instance associated with that Scope.

Singleton instances SHALL NOT be affected.

---

# 18. Shutdown Process

## 18.1 Overview

Shutdown is the deterministic termination of the Runtime.

Shutdown SHALL execute exactly once.

---

## 18.2 Shutdown Stages

The Shutdown process SHALL execute the following stages:

1. Reject new Resolution Requests.
2. Dispose active Scopes.
3. Release Runtime-owned shared instances.
4. Release internal Runtime resources.
5. Transition to Disposed.

---

## 18.3 Shutdown Guarantees

Shutdown SHALL preserve Runtime consistency.

Shutdown SHALL complete even if one resource disposal fails, provided failures are reported through the Runtime exception model.

---

# 19. Runtime Exception Model

## 19.1 General Rule

Every Runtime orchestration failure SHALL be represented by a typed Runtime exception.

Returning `null` or error codes is prohibited.

---

## 19.2 Failure Categories

| Category              | Description                                                        |
| --------------------- | ------------------------------------------------------------------ |
| Bootstrap Failure     | Runtime initialization failed.                                     |
| Invalid Runtime State | Requested operation is not permitted in the current Runtime state. |
| Provider Failure      | Mandatory Service Provider failed.                                 |
| Module Failure        | Module initialization failed.                                      |
| Shutdown Failure      | Runtime shutdown encountered unrecoverable errors.                 |

---

# 20. Additional Runtime Rules

## RT-011 — Runtime State Ownership

A Runtime SHALL exist in exactly one Runtime State.

---

## RT-012 — Deterministic State Transitions

Runtime state transitions SHALL be deterministic.

---

## RT-013 — One Bootstrap

Bootstrap SHALL NOT execute more than once.

---

## RT-014 — Runtime Freeze

After entering the Frozen state, Registration Operations SHALL remain permanently unavailable.

---

## RT-015 — Running Runtime

Only a Running Runtime SHALL accept Resolution Requests.

---

## RT-016 — Scope Ownership

Every Scope SHALL belong to exactly one Runtime.

---

## RT-017 — Scope Isolation

Scoped instances SHALL NOT be shared across different Scopes.

---

## RT-018 — Deterministic Shutdown

Equivalent Runtime executions SHALL produce equivalent Shutdown sequences.

---

## RT-019 — Runtime Disposal

Disposed Runtime instances SHALL reject every subsequent operation.

---

## RT-020 — Event Ordering

Runtime lifecycle events SHALL preserve the order defined by this specification.

---

## RT-021 — Provider Isolation

Failure in one Service Provider SHALL NOT leave the Runtime in a partially initialized operational state.

---

## RT-022 — Module Isolation

Modules SHALL communicate only through documented Runtime contracts.

---

## RT-023 — Runtime Consistency

Every Runtime transition SHALL preserve Runtime invariants.

---

## RT-024 — Failure Visibility

Runtime failures SHALL be observable only through the typed Runtime exception hierarchy.

---

## RT-025 — Deterministic Runtime

Equivalent Runtime configurations SHALL produce equivalent observable Runtime behavior.

---

# 21. Rule Index

This section provides the canonical index of every Runtime Rule defined by this specification.

Runtime Rule identifiers are permanent and SHALL NOT be renumbered or reused.

| Rule   | Title                            |
| ------ | -------------------------------- |
| RT-001 | Bootstrap Once                   |
| RT-002 | Deterministic Bootstrap          |
| RT-003 | Registration Window              |
| RT-004 | Freeze Before Resolution         |
| RT-005 | Frozen Runtime                   |
| RT-006 | Deterministic Provider Execution |
| RT-007 | Module Dependency Order          |
| RT-008 | Finalization Consistency         |
| RT-009 | Runtime Isolation                |
| RT-010 | Bootstrap Failure                |
| RT-011 | Runtime State Ownership          |
| RT-012 | Deterministic State Transitions  |
| RT-013 | One Bootstrap                    |
| RT-014 | Runtime Freeze                   |
| RT-015 | Running Runtime                  |
| RT-016 | Scope Ownership                  |
| RT-017 | Scope Isolation                  |
| RT-018 | Deterministic Shutdown           |
| RT-019 | Runtime Disposal                 |
| RT-020 | Event Ordering                   |
| RT-021 | Provider Isolation               |
| RT-022 | Module Isolation                 |
| RT-023 | Runtime Consistency              |
| RT-024 | Failure Visibility               |
| RT-025 | Deterministic Runtime            |

---

# 22. Traceability Matrix

## 22.1 Purpose

Every Runtime Rule SHALL be traceable to:

* Runtime Invariants;
* Runtime lifecycle stages;
* implementation responsibilities;
* automated verification scenarios.

## 22.2 Traceability

| Rules         | Related Invariants | Planned Responsibility | Required Verification                              |
| ------------- | ------------------ | ---------------------- | -------------------------------------------------- |
| RT-001–RT-004 | INV-RT02, INV-RT03 | Bootstrap coordination | Bootstrap sequencing tests                         |
| RT-005–RT-010 | INV-RT04, INV-RT05 | Runtime finalization   | Registration freeze and startup tests              |
| RT-011–RT-015 | INV-RT06           | Runtime state machine  | State transition validation tests                  |
| RT-016–RT-017 | INV-RT05           | Scope lifecycle        | Scope ownership and isolation tests                |
| RT-018–RT-020 | INV-RT07           | Shutdown and events    | Shutdown ordering and lifecycle event tests        |
| RT-021–RT-025 | INV-RT08           | Runtime consistency    | Failure isolation and deterministic behavior tests |

---

# 23. Implementation Checklist

An implementation SHALL NOT claim conformance until every mandatory checklist item is complete.

## 23.1 Bootstrap

* [ ] Bootstrap executes exactly once.
* [ ] Bootstrap stages execute in normative order.
* [ ] Registration Window is enforced.
* [ ] Runtime freezes successfully.

## 23.2 Runtime States

* [ ] Runtime exists in exactly one state.
* [ ] Invalid transitions are rejected.
* [ ] Frozen Runtime rejects registration.
* [ ] Running Runtime accepts resolution only.

## 23.3 Providers and Modules

* [ ] Providers execute deterministically.
* [ ] Module dependencies are respected.
* [ ] Provider failures abort bootstrap.
* [ ] Modules interact only through documented contracts.

## 23.4 Scopes

* [ ] Scope ownership is enforced.
* [ ] Scoped instances remain isolated.
* [ ] Scope disposal releases owned resources.

## 23.5 Shutdown

* [ ] Shutdown executes exactly once.
* [ ] Shutdown follows the specified sequence.
* [ ] Runtime reaches Disposed state.
* [ ] Runtime-owned resources are released.

## 23.6 Verification

* [ ] Every Runtime Rule has automated verification.
* [ ] Every Runtime Invariant is covered by tests.
* [ ] Static analysis passes.
* [ ] Coding standards pass.
* [ ] Regression tests pass.

---

# 24. Conformance

An implementation MAY declare conformance with this specification only when:

* every Runtime Rule is implemented;
* every Runtime Invariant is preserved;
* Runtime lifecycle follows this specification;
* automated verification succeeds.

---

# 25. Compatibility

The following Runtime behaviors are compatibility protected:

* Bootstrap lifecycle;
* Registration Window;
* Runtime state transitions;
* Frozen Runtime semantics;
* Scope ownership;
* Shutdown sequence;
* Runtime event ordering.

Breaking these behaviors requires an approved ADR.

---

# 26. Change Impact

Changes to this specification may affect:

* Bootstrap implementation;
* Service Provider execution;
* Module loading;
* Runtime state machine;
* Scope management;
* Shutdown implementation;
* Runtime diagnostics;
* automated verification.

Architectural changes require an approved ADR.

---

# 27. Implementation Notes

This section is informative.

Implementations MAY optimize Bootstrap execution, Provider discovery and Module loading provided that observable Runtime behavior remains unchanged.

Internal implementation details are not compatibility protected.

Observable Runtime behavior is.

---

# 28. Acceptance Criteria

This specification is ready for Release Candidate status when:

* Bootstrap is fully specified;
* Runtime lifecycle is complete;
* Runtime state machine is complete;
* Scope lifecycle is defined;
* Shutdown process is defined;
* Runtime Rules are complete;
* Traceability Matrix is complete;
* Implementation Checklist is complete;
* no unresolved architectural decisions remain.

---

# 29. Revision History

| Version | Date       | Status           | Description                                                                                                                                               |
| ------- | ---------- | ---------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 0.1.0   | 2026-07-16 | Approved         | Initial Runtime concepts, lifecycle and invariants.                                                                                                       |
| 0.2.0   | 2026-07-16 | Approved         | Added Bootstrap Process, Provider Orchestration, Module Orchestration, Runtime Finalization and RT-001 through RT-010.                                    |
| 0.3.0   | 2026-07-16 | Approved         | Added Runtime State Machine, Runtime Events, Scope Lifecycle, Shutdown Process, Runtime Exception Model and RT-011 through RT-025.                        |
| 0.4.0   | 2026-07-16 | Draft for Review | Added Rule Index, Traceability Matrix, Implementation Checklist, Conformance, Compatibility, Change Impact, Implementation Notes and Acceptance Criteria. |

---

# End of Specification

