---
id: AGGREGATE-BOUNDARIES
title: Aggregate Boundaries
summary: **Specification:** SPEC-WP-004-DI-CONTAINER.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-17
updated: 2026-07-22
tags:
  - aggregate
  - boundaries
work_package: WP-004
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# WP-004 — Aggregate Boundaries

**Specification:** SPEC-WP-004-DI-CONTAINER

**Document:** Aggregate Boundaries

**Status:** Approved

**Version:** 1.0.0

---

# 1. Purpose

This document defines the aggregate boundaries of the Dependency Injection Container domain.

Its objective is to establish ownership, consistency rules and transactional boundaries before implementation begins.

The aggregate model defined herein is normative.

---

# 2. Aggregate Root

The Dependency Injection domain defines exactly one Aggregate Root.

```
Container
```

Every modification to the domain SHALL occur through the Container.

No other aggregate root exists in WP-004.

---

# 3. Aggregate Composition

The Container Aggregate contains the following concepts.

## Entities

- Container
- Binding
- Alias

## Value Objects

- ServiceIdentifier
- BindingType
- Lifetime
- BindingMetadata
- ResolutionContext
- ResolutionPath
- ResolutionPolicy

The Aggregate SHALL preserve the consistency of all contained entities and value objects.

---

# 4. Aggregate Responsibilities

The Container Aggregate is responsible for:

- registering services;
- replacing bindings;
- removing bindings;
- validating aliases;
- coordinating resolution;
- preserving domain invariants.

The Aggregate SHALL NOT:

- resolve HTTP requests;
- manage configuration;
- access databases;
- dispatch events;
- execute console commands.

---

# 5. Aggregate Ownership

Ownership is exclusive.

```
Framework
    │
    ▼
Application
    │
    ▼
Container
    │
    ├── Binding
    ├── Alias
    └── Value Objects
```

Bindings SHALL NOT exist outside a Container.

Aliases SHALL NOT exist outside a Container.

---

# 6. Consistency Boundary

The Container defines one consistency boundary.

Every state transition SHALL preserve every invariant defined by the Aggregate.

Partial updates are prohibited.

---

# 7. Lifecycle

The Aggregate lifecycle is identical to the Application lifecycle.

```
Application Created
        │
        ▼
Container Created
        │
        ▼
Service Registration
        │
        ▼
Resolution
        │
        ▼
Shutdown
        │
        ▼
Container Destroyed
```

No Aggregate instance SHALL survive Application termination.

---

# 8. Internal Collaboration

The Aggregate collaborates with Domain Services.

```
Container
        │
        ├────────► ResolutionEngine
        ├────────► BindingValidator
        ├────────► AliasResolver
        └────────► FactoryResolver
```

These services SHALL NOT own aggregate state.

---

# 9. Infrastructure Boundary

Infrastructure components are outside the Aggregate.

Examples include:

- BindingRepository
- AliasRepository
- SingletonRepository
- ResolutionCache

These components MAY change without modifying the domain model.

---

# 10. Invariants

The Aggregate SHALL preserve the following invariants.

## AG-001

Exactly one Binding exists for each canonical ServiceIdentifier.

---

## AG-002

Every Alias references an existing canonical ServiceIdentifier.

---

## AG-003

Aliases SHALL NOT reference themselves.

---

## AG-004

Alias graphs SHALL be acyclic.

---

## AG-005

Bindings are immutable after registration.

---

## AG-006

ServiceIdentifier is immutable.

---

## AG-007

Singleton instances belong to one Binding only.

---

## AG-008

ResolutionPath SHALL NOT contain cycles.

---

## AG-009

Container lifetime equals Application lifetime.

---

# 11. State Ownership

The Aggregate owns:

- registrations;
- aliases;
- singleton instances;
- resolution coordination.

The Aggregate does NOT own:

- provider execution;
- runtime state;
- framework lifecycle.

---

# 12. Extension Rules

Future Work Packages MAY extend the Aggregate only through documented extension points.

Extensions SHALL preserve every invariant defined herein.

---

# 13. Acceptance Criteria

This document is complete when:

- the Aggregate Root is identified;
- ownership is defined;
- consistency boundaries are established;
- infrastructure is excluded from the domain;
- invariants are complete.

---

# End of Document