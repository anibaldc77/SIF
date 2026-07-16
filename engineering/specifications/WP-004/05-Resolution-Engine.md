# WP-004 — Resolution Engine

**Document ID:** WP-004-05

**Work Package:** WP-004 — Runtime Composition Engine

**Version:** 0.2.0

**Status:** Draft for Review

**Category:** Normative Specification

**Author:** SIF Architecture Board

---

# Executive Summary

This specification defines the Resolution Engine of the SIF Runtime Composition Engine.

The Resolution Engine is responsible for transforming registration metadata into runtime service instances through deterministic dependency resolution.

This specification defines:

* the resolution lifecycle;
* the resolution context;
* dependency traversal;
* lifetime-aware resolution;
* resolution invariants;
* observable resolution behavior.

This document is normative.

Every implementation of the Runtime Composition Engine SHALL conform to this specification.

---

# 1. Purpose

The purpose of this specification is to define a deterministic, implementation-independent model for runtime service resolution.

The Resolution Engine transforms previously validated Binding definitions into runtime objects.

The Resolution Engine SHALL operate exclusively on successfully registered Bindings.

---

# 2. Scope

This specification defines:

* service resolution;
* dependency traversal;
* resolution context;
* lifetime-aware resolution;
* recursive dependency resolution;
* resolution state transitions.

This specification does **not** define:

* Binding registration;
* Binding replacement;
* Binding removal;
* registration validation.

Those responsibilities are defined by **WP-004-04 — Binding and Registration Model**.

---

# 3. Relationship with the Runtime Composition Engine

The Runtime Composition Engine is composed of three complementary subsystems.

| Part | Specification                  | Responsibility                                                |
| ---- | ------------------------------ | ------------------------------------------------------------- |
| I    | Binding and Registration Model | Defines composition metadata                                  |
| II   | Resolution Engine              | Produces runtime service instances                            |
| III  | Runtime Integration            | Integrates runtime composition with the application lifecycle |

This document specifies **Part II**.

---

# 4. Resolution Concepts

The Resolution Engine operates on four fundamental concepts.

## 4.1 Resolution Request

A Resolution Request represents the intention to obtain the runtime instance associated with a canonical Service Identifier.

Every Resolution Request SHALL target exactly one canonical Service Identifier.

---

## 4.2 Resolution Context

The Resolution Context represents the internal execution state of a single resolution operation.

The Resolution Context SHALL encapsulate:

* the active resolution request;
* the current dependency traversal;
* the resolution stack;
* lifetime-aware state;
* implementation-defined diagnostic information.

The Resolution Context is an internal Runtime concept.

It SHALL NOT be exposed as part of the public Container API.

---

## 4.3 Resolution Stack

The Resolution Stack represents the ordered sequence of services currently being resolved.

The Resolution Stack exists exclusively during an active Resolution Request.

Its primary responsibilities are:

* recursive dependency tracking;
* circular dependency detection;
* diagnostic support.

---

## 4.4 Dependency Graph

The Dependency Graph represents the directed graph formed by runtime service dependencies during a Resolution Request.

The Resolution Engine SHALL traverse the Dependency Graph deterministically.

---

# 5. Resolution Pipeline

Every Resolution Request SHALL execute the following conceptual pipeline.

```text
Resolution Request
        │
        ▼
Binding Lookup
        │
        ▼
Lifetime Evaluation
        │
        ▼
Dependency Resolution
        │
        ▼
Instance Creation
        │
        ▼
Post-Construction
        │
        ▼
Resolved Instance
```

Each stage SHALL complete successfully before the next stage begins.

Failure at any stage SHALL terminate the current Resolution Request.

---

# 6. Resolution Design Goals

The Resolution Engine is designed to satisfy the following objectives.

* deterministic resolution;
* recursive dependency composition;
* lifetime consistency;
* implementation independence;
* reproducible diagnostics;
* complete traceability;
* predictable runtime behavior.

---

# 7. Architectural Principles

The Resolution Engine follows these architectural principles.

1. Resolution SHALL operate exclusively on registered Bindings.
2. Resolution SHALL NOT mutate Binding definitions.
3. Resolution SHALL be deterministic.
4. Lifetime Policies SHALL govern instance ownership.
5. Circular dependency detection SHALL precede infinite recursion.
6. Resolution SHALL preserve Container consistency.
7. Runtime behavior SHALL remain reproducible for equivalent inputs.

---

# 8. Resolution Lifecycle

Every Resolution Request progresses through the following conceptual states.

```text
Created
    │
    ▼
Validated
    │
    ▼
Resolving
    │
    ▼
Resolved
```

A failed Resolution Request SHALL terminate in a Failure state.

The Resolution Engine SHALL guarantee that unsuccessful Resolution Requests do not expose partially resolved runtime state.

---

# 9. Resolution Invariants

The Resolution Engine SHALL preserve the following invariants.

| ID       | Invariant                                                                 |
| -------- | ------------------------------------------------------------------------- |
| INV-RE01 | Resolution always begins from one canonical Service Identifier.           |
| INV-RE02 | Every Resolution Context belongs to exactly one Resolution Request.       |
| INV-RE03 | Resolution SHALL NOT modify registered Bindings.                          |
| INV-RE04 | Dependency traversal is deterministic.                                    |
| INV-RE05 | Resolution Stack correctly represents active recursive traversal.         |
| INV-RE06 | Circular dependencies SHALL be detected before infinite recursion occurs. |
| INV-RE07 | Lifetime Policies SHALL be respected during every Resolution Request.     |
| INV-RE08 | Failed Resolution Requests SHALL NOT expose partial runtime state.        |

# 10. Resolution Algorithm

## 10.1 Overview

The Resolution Algorithm defines the normative sequence of operations required to transform a registered Binding into a runtime service instance.

Every Resolution Request SHALL execute the same conceptual algorithm regardless of the underlying implementation.

Observable behavior SHALL remain deterministic.

---

## 10.2 Algorithm Stages

The Resolution Engine SHALL execute the following stages in order:

1. Resolve canonical Service Identifier.
2. Retrieve the associated Binding.
3. Evaluate the Lifetime Policy.
4. Reuse an existing instance when permitted.
5. Detect circular dependencies.
6. Resolve constructor dependencies.
7. Create the service instance.
8. Perform post-construction processing.
9. Store shared instances when required.
10. Return the resolved instance.

No implementation MAY alter the observable order of these stages.

---

# 11. Resolution Context Model

## 11.1 Responsibilities

The Resolution Context SHALL coordinate the execution of a single Resolution Request.

Its responsibilities include:

* tracking dependency traversal;
* maintaining the Resolution Stack;
* controlling Lifetime evaluation;
* providing diagnostic information;
* preserving deterministic execution.

The Resolution Context SHALL be discarded after the Resolution Request completes.

---

## 11.2 Isolation

Each Resolution Request SHALL own an independent Resolution Context.

Resolution Contexts SHALL NOT be shared between concurrent Resolution Requests.

---

# 12. Dependency Resolution

## 12.1 Recursive Resolution

Dependencies SHALL be resolved recursively.

Every dependency SHALL itself become a Resolution Request executed within the active Resolution Context.

---

## 12.2 Resolution Order

Dependency traversal SHALL be deterministic.

Equivalent dependency graphs SHALL produce equivalent traversal sequences.

---

## 12.3 Constructor Resolution

Constructor parameters participating in dependency injection SHALL be resolved before instance creation.

Failure to resolve any mandatory dependency SHALL terminate the current Resolution Request.

---

# 13. Lifetime Resolution

## 13.1 General Rule

Lifetime Policies determine whether an existing instance may be reused.

Lifetime evaluation SHALL occur before instance creation.

---

## 13.2 Transient

A Transient Binding SHALL always produce a new instance.

Previously created instances SHALL NOT be reused.

---

## 13.3 Singleton

A Singleton Binding SHALL reuse the same shared instance throughout the lifetime of the owning Container.

If no shared instance exists, one SHALL be created.

---

## 13.4 Scoped

A Scoped Binding SHALL reuse one instance within the active Scope.

Outside that Scope, a different instance MAY exist.

Scope management is specified by WP-004-06.

---

# 14. Resolution Rules

## RE-001 — Canonical Resolution

**Statement**

Resolution SHALL begin with one canonical Service Identifier.

**Rationale**

Guarantees deterministic entry into the Resolution Engine.

---

## RE-002 — Registered Bindings Only

**Statement**

Only successfully registered Bindings SHALL participate in Resolution.

**Rationale**

Prevents undefined runtime behavior.

---

## RE-003 — Immutable Registration

**Statement**

Resolution SHALL NOT modify Binding definitions.

**Rationale**

Preserves registration consistency.

---

## RE-004 — Deterministic Traversal

**Statement**

Equivalent dependency graphs SHALL produce equivalent traversal sequences.

**Rationale**

Supports reproducible execution.

---

## RE-005 — Lifetime Before Construction

**Statement**

Lifetime evaluation SHALL precede object creation.

**Rationale**

Prevents unnecessary instantiation.

---

## RE-006 — Recursive Resolution

**Statement**

Every dependency SHALL be resolved using the same Resolution Algorithm.

**Rationale**

Maintains behavioral consistency.

---

## RE-007 — Constructor Completeness

**Statement**

Every mandatory constructor dependency SHALL be resolved before instance creation.

**Rationale**

Guarantees valid object construction.

---

## RE-008 — Resolution Context Isolation

**Statement**

Every Resolution Request SHALL execute within its own Resolution Context.

**Rationale**

Supports deterministic and thread-safe execution.

---

## RE-009 — No Partial Resolution

**Statement**

A failed Resolution Request SHALL NOT expose partially resolved runtime state.

**Rationale**

Preserves Runtime consistency.

---

## RE-010 — Deterministic Result

**Statement**

Equivalent Resolution Requests executed against equivalent Container state SHALL produce equivalent observable results.

**Rationale**

Supports verification and reproducibility.

---

# Revision History

| Version | Date       | Status           | Description                                                                                                                 |
| ------- | ---------- | ---------------- | --------------------------------------------------------------------------------------------------------------------------- |
| 0.1.0   | 2026-07-16 | Approved         | Initial specification defining purpose, scope, concepts, resolution pipeline, lifecycle and invariants.                     |
| 0.2.0   | 2026-07-16 | Draft for Review | Added Resolution Algorithm, Resolution Context Model, Dependency Resolution, Lifetime Resolution and RE-001 through RE-010. |

