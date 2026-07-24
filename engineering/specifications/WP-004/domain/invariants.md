---
id: DOMAIN-INVARIANTS
title: Domain Invariants
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
  - domain
  - invariants
work_package: WP-004
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# WP-004 — Domain Invariants

**Specification:** SPEC-WP-004-DI-CONTAINER

**Document:** Domain Invariants

**Status:** Approved

**Version:** 1.0.0

---

# 1. Purpose

This document defines the immutable business rules governing the Dependency Injection Container domain.

Every implementation SHALL preserve every invariant described herein.

Violating an invariant constitutes a defect in the implementation.

---

# 2. Definition

A Domain Invariant is a rule that SHALL remain true before and after every observable operation performed by the Container.

Operations include:

- registration;
- replacement;
- removal;
- alias creation;
- resolution;
- shutdown.

---

# 3. Aggregate Invariants

## INV-001 — Unique Service Identifier

Each canonical ServiceIdentifier SHALL identify exactly one Binding.

Duplicate canonical bindings are prohibited.

---

## INV-002 — Immutable Service Identifier

ServiceIdentifier is immutable.

Its value SHALL NEVER change after creation.

---

## INV-003 — Immutable Binding

Bindings SHALL remain immutable after registration.

Updating a binding SHALL replace it.

It SHALL NOT mutate it.

---

## INV-004 — Alias References Existing Binding

Every Alias SHALL reference an existing canonical ServiceIdentifier.

Dangling aliases are prohibited.

---

## INV-005 — Alias Self Reference

Aliases SHALL NOT reference themselves.

```
A -> A
```

is invalid.

---

## INV-006 — Alias Cycles

Alias graphs SHALL be acyclic.

The following is invalid:

```
A -> B

B -> C

C -> A
```

---

## INV-007 — Deterministic Resolution

Given the same Container state and the same ServiceIdentifier:

Resolution SHALL always produce the same observable behavior.

---

## INV-008 — Singleton Identity

Every Singleton Binding SHALL own exactly one instance.

The instance SHALL be reused.

---

## INV-009 — Transient Identity

Transient Bindings SHALL create a new object for every successful resolution.

---

## INV-010 — Existing Instance Identity

Instance Bindings SHALL always return the supplied object.

The Container SHALL NEVER replace it.

---

## INV-011 — Factory Ownership

Factories SHALL NOT own Container state.

Factories MAY create objects.

Factories SHALL NOT register services.

---

## INV-012 — Resolution Context

Every resolution SHALL own exactly one ResolutionContext.

Contexts SHALL NOT be shared.

---

## INV-013 — Resolution Path

ResolutionPath SHALL represent one resolution only.

Resolution paths SHALL NOT survive resolution completion.

---

## INV-014 — Circular Dependency Detection

Circular dependencies SHALL be detected before stack overflow occurs.

Undefined recursive execution is prohibited.

---

## INV-015 — Aggregate Ownership

Bindings SHALL belong to exactly one Container.

Aliases SHALL belong to exactly one Container.

---

## INV-016 — Container Lifetime

Container lifetime SHALL equal Application lifetime.

Containers SHALL NOT outlive Applications.

---

## INV-017 — Explicit Registration

Every Binding SHALL originate from an explicit registration.

Automatic registration is prohibited.

---

## INV-018 — Explicit Resolution

Every object managed by the Container SHALL originate from an explicit resolution request.

Implicit resolution is prohibited.

---

## INV-019 — Runtime Isolation

The Container SHALL NOT mutate Runtime state.

Runtime SHALL remain outside the Aggregate.

---

## INV-020 — Infrastructure Independence

The domain SHALL remain independent from:

- repositories;
- caches;
- storage;
- PHP collections;
- implementation details.

---

# 4. Behavioral Consequences

Whenever an invariant would be violated:

the operation SHALL fail.

Partial success is prohibited.

Silent correction is prohibited.

---

# 5. Verification Requirements

Every invariant SHALL have:

- one or more PHPUnit tests;
- implementation traceability;
- quality gate verification.

---

# 6. Traceability

The implementation report SHALL reference invariants by identifier.

Example:

```
INV-008 verified by:

ContainerSingletonTest::testSingletonReturnsSameInstance()
```

This traceability is mandatory.

---

# 7. Future Compatibility

Future Work Packages MAY introduce new invariants.

Existing invariant identifiers SHALL NEVER change.

Identifiers are permanent.

---

# 8. Acceptance Criteria

This document is complete when:

- every Aggregate invariant is uniquely identified;
- every invariant is testable;
- every invariant is implementation-independent;
- traceability requirements are defined.

---

# End of Document