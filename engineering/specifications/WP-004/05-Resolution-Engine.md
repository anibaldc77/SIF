# WP-004 — Resolution Engine

**Document ID:** WP-004-05

**Work Package:** WP-004 — Runtime Composition Engine

**Version:** 0.1.0

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

---

# Revision History

| Version | Date       | Status           | Description                                                                                             |
| ------- | ---------- | ---------------- | ------------------------------------------------------------------------------------------------------- |
| 0.1.0   | 2026-07-16 | Draft for Review | Initial specification defining purpose, scope, concepts, resolution pipeline, lifecycle and invariants. |
