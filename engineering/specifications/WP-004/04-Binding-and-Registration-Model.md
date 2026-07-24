---
id: WP-004-04-BINDING-AND-REGISTRATION-MODEL
title: Binding and Registration Model
summary: **Work Package:** WP-004 — Runtime Composition Engine.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-16
updated: 2026-07-22
tags:
  - binding
  - registration
  - model
work_package: WP-004
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# WP-004 — Binding and Registration Model

**Document ID:** WP-004-04

**Work Package:** WP-004 — Runtime Composition Engine

**Version:** 1.0.0-rc.1

**Status:** Release Candidate

**Category:** Normative Specification

**Author:** SIF Architecture Board

---

# Executive Summary

This document specifies the **Binding and Registration Model** of the SIF Runtime Composition Engine.

The Binding and Registration Model defines how services become part of the runtime composition graph through deterministic registration rules.

This specification establishes:

* the registration lifecycle;
* the binding model;
* supported binding strategies;
* lifetime policies;
* validation requirements;
* registration invariants;
* failure conditions.

This document is normative.

Every implementation of the Runtime Composition Engine SHALL conform to this specification.

---

# 1. Purpose

The purpose of this specification is to define a deterministic and implementation-independent model for registering runtime services.

Registration is the process by which composition rules become part of the runtime.

The specification guarantees that identical registration operations produce identical runtime metadata.

---

# 2. Scope

This document specifies:

* Service registration
* Binding registration
* Binding replacement
* Alias registration
* Lifetime assignment
* Registration validation
* Registration state transitions

This document does **not** specify:

* dependency resolution;
* object construction;
* runtime activation;
* provider discovery.

These behaviors are defined by subsequent WP-004 specifications.

---

# 3. Relationship with the Runtime Composition Engine

The Runtime Composition Engine is composed of three complementary models:

| Part | Specification                  | Responsibility                                       |
| ---- | ------------------------------ | ---------------------------------------------------- |
| I    | Binding and Registration Model | Defines composition metadata                         |
| II   | Resolution Engine              | Executes runtime composition                         |
| III  | Runtime Integration            | Integrates the engine into the application lifecycle |

This document defines **Part I**.

---

# 4. Normative References

This specification depends upon:

* SIF Constitution
* SIF Architecture Specification (SAS)
* WP-004 Domain Model
* WP-004 Foundation
* WP-004 Architecture
* WP-004 Contracts

---

# 5. Definitions

## 5.1 Registration

The process by which a composition rule becomes available to the Runtime Composition Engine.

---

## 5.2 Binding

A composition rule associating a Service Identifier with an implementation strategy.

---

## 5.3 Service Identifier

The canonical identifier used by the Runtime Composition Engine to uniquely identify a service.

---

## 5.4 Binding Strategy

The mechanism used to create or obtain service instances.

Examples include:

* implementation type
* factory
* existing instance
* alias

---

## 5.5 Lifetime Policy

The ownership policy governing instance reuse.

Lifetime policies are independent from creation strategies.

---

# 6. Design Goals

The Binding and Registration Model pursues the following objectives:

* Deterministic behavior
* Explicit configuration
* Technology independence
* Runtime consistency
* Predictable validation
* High traceability
* Support for automated verification

---

# 7. Architectural Principles

The Binding and Registration Model follows these principles:

1. Every binding has one canonical Service Identifier.
2. Every registration is explicit.
3. Registration never creates runtime instances.
4. Registration only creates composition metadata.
5. Validation precedes registration.
6. Registration is deterministic.
7. Runtime state remains internally consistent.

---

# 8. Registration Lifecycle

Every registration progresses through the following conceptual states:

```text
Created
    │
    ▼
Validated
    │
    ▼
Registered
    │
    ▼
Available
```

Transitions that violate the invariants defined by this specification SHALL be rejected.

---

# 9. Registration Invariants

The Runtime Composition Engine shall preserve the following invariants during registration.

| ID      | Invariant                                                                           |
| ------- | ----------------------------------------------------------------------------------- |
| INV-R01 | Every Binding has exactly one canonical Service Identifier.                         |
| INV-R02 | Every Service Identifier uniquely identifies one registration.                      |
| INV-R03 | Registration never instantiates runtime objects.                                    |
| INV-R04 | Validation precedes registration.                                                   |
| INV-R05 | Failed registrations produce no partial state.                                      |
| INV-R06 | Registration operations are atomic.                                                 |
| INV-R07 | Runtime metadata remains internally consistent after every successful registration. |

---

# Revision History

| Version | Date       | Description            |
| ------- | ---------- | ---------------------- |
| 0.1.0   | 2026-07-16 | Initial draft created. |

# 10. Binding Model

## 10.1 Overview

A Binding represents the fundamental composition rule managed by the Runtime Composition Engine.

A Binding associates a canonical Service Identifier with an implementation strategy and a lifetime policy. It contains only composition metadata and never represents a runtime instance.

The Binding Model is immutable after successful registration.

---

## 10.2 Design Objectives

The Binding Model is designed to satisfy the following objectives:

* deterministic registration;
* explicit composition;
* implementation independence;
* immutable runtime metadata;
* traceable composition rules;
* extensibility without breaking compatibility.

---

## 10.3 Conceptual Model

A Binding is composed of the following logical elements:

| Element            | Required | Description                          |
| ------------------ | -------- | ------------------------------------ |
| Service Identifier | Yes      | Canonical identifier of the service. |
| Binding Strategy   | Yes      | Defines how the service is obtained. |
| Lifetime Policy    | Yes      | Defines instance ownership.          |
| Metadata           | Yes      | Internal registration metadata.      |
| Tags               | Optional | Logical categorization.              |
| Aliases            | Optional | Secondary identifiers.               |

A Binding SHALL contain exactly one canonical Service Identifier.

Aliases SHALL NOT replace the canonical identifier.

---

## 10.4 Binding Immutability

Once registration has completed successfully, the Binding definition becomes immutable.

Modifications SHALL require a new registration operation.

The Runtime Composition Engine SHALL NOT mutate Binding metadata during dependency resolution.

---

# 11. Binding Strategies

## 11.1 Overview

Binding Strategies define how a runtime service is produced.

Strategies define composition behavior only.

They do not execute object construction during registration.

---

## 11.2 Supported Strategies

The Runtime Composition Engine defines four standard Binding Strategies.

| Strategy       | Description                                         |
| -------------- | --------------------------------------------------- |
| Implementation | Instantiates an implementation type.                |
| Factory        | Delegates creation to a factory.                    |
| Instance       | Uses an existing object instance.                   |
| Alias          | Redirects resolution to another Service Identifier. |

Implementations MAY define additional strategies provided that they preserve all invariants established by this specification.

---

## 11.3 Implementation Binding

Implementation Binding associates a Service Identifier with a concrete implementation type.

The implementation type SHALL satisfy the corresponding service contract.

---

## 11.4 Factory Binding

Factory Binding delegates instance creation to a factory object or callable.

Factories SHALL execute only during dependency resolution.

Factories SHALL NOT execute during registration.

---

## 11.5 Instance Binding

Instance Binding associates a Service Identifier with an already existing runtime instance.

Ownership of externally supplied instances remains implementation-defined.

---

## 11.6 Alias Binding

Alias Binding redirects one Service Identifier to another canonical Service Identifier.

Alias chains SHALL remain acyclic.

Circular aliases SHALL be rejected during validation.

---

# 12. Lifetime Policies

## 12.1 Overview

Lifetime Policies define instance ownership.

Lifetime Policies are independent from Binding Strategies.

The same Binding Strategy may be associated with different Lifetime Policies.

---

## 12.2 Standard Lifetime Policies

The Runtime Composition Engine defines the following standard policies.

| Policy    | Description                                                   |
| --------- | ------------------------------------------------------------- |
| Transient | A new instance is produced for each resolution.               |
| Singleton | One shared instance exists for the lifetime of the container. |
| Scoped    | One instance exists per logical execution scope.              |

Additional policies MAY be introduced through implementation extensions without modifying this specification.

---

## 12.3 Lifetime Independence

Lifetime Policies SHALL NOT affect registration.

They influence only runtime composition behavior.

Registration metadata remains identical regardless of the selected Lifetime Policy.

---

# 13. Registration Metadata

Each successful registration produces immutable metadata describing the composition rule.

Registration metadata SHALL include, at minimum:

| Metadata                     | Description                    |
| ---------------------------- | ------------------------------ |
| Registration Identifier      | Unique registration reference. |
| Registration Timestamp       | Registration creation time.    |
| Binding Strategy             | Registered strategy.           |
| Lifetime Policy              | Selected lifetime.             |
| Validation Status            | Successful validation result.  |
| Canonical Service Identifier | Primary identifier.            |

Implementations MAY extend registration metadata with implementation-specific information.

---

# 14. Registration Rules

## RR-001 — Canonical Registration

**Statement**

Every Binding SHALL define exactly one canonical Service Identifier.

**Rationale**

Guarantees deterministic lookup.

---

## RR-002 — Unique Service Identifier

**Statement**

Two active Bindings SHALL NOT share the same canonical Service Identifier.

**Rationale**

Preserves registration uniqueness.

---

## RR-003 — Atomic Registration

**Statement**

Registration SHALL complete atomically.

Partial registration state SHALL NOT be observable.

**Rationale**

Maintains runtime consistency.

---

## RR-004 — Immutable Binding Definition

**Statement**

A successfully registered Binding SHALL become immutable.

**Rationale**

Ensures deterministic runtime metadata.

---

## RR-005 — Validation Before Registration

**Statement**

Validation SHALL complete successfully before registration occurs.

**Rationale**

Prevents inconsistent composition metadata.

---

## RR-006 — Deterministic Metadata

**Statement**

Equivalent registration operations SHALL generate equivalent Binding metadata.

**Rationale**

Supports reproducibility, verification and traceability.

# 15. Registration Operations

## 15.1 Overview

The Registration Model defines four state-changing operations:

| Operation | Responsibility                                                            |
| --------- | ------------------------------------------------------------------------- |
| Register  | Incorporates a new Binding into the Container Aggregate.                  |
| Replace   | Atomically substitutes an existing Binding.                               |
| Remove    | Eliminates an existing Binding and its dependent registration state.      |
| Alias     | Associates an alternate Service Identifier with a canonical registration. |

Every Registration Operation SHALL execute within the consistency boundary of one Container Aggregate.

A Registration Operation SHALL either complete successfully or leave the Aggregate unchanged.

---

## 15.2 Register Operation

The Register Operation incorporates a new Binding into the Container Aggregate.

The operation SHALL receive a complete and valid Binding definition.

Registration SHALL fail when the canonical Service Identifier is already associated with an active Binding, unless the operation was explicitly initiated as a Replace Operation.

Registration SHALL NOT instantiate the associated Service.

Registration SHALL NOT execute factories.

Registration SHALL NOT perform dependency resolution.

---

## 15.3 Replace Operation

The Replace Operation atomically substitutes an existing Binding with a new Binding that owns the same canonical Service Identifier.

Replacement SHALL preserve the identity of the canonical Service Identifier.

Replacement SHALL create a new immutable Binding definition.

The previous Binding SHALL transition to the Replaced state.

The replacement Binding SHALL transition to the Registered state only after validation succeeds.

If validation or replacement fails, the previous Binding SHALL remain active and unchanged.

---

## 15.4 Remove Operation

The Remove Operation eliminates an active Binding from the Container Aggregate.

Removal SHALL invalidate all aliases that directly or indirectly resolve to the removed canonical Service Identifier.

Removal SHALL discard any shared instance owned by the removed Binding.

Removal SHALL NOT destroy externally owned objects unless ownership was explicitly transferred to the Container by a future approved specification.

Removing an unknown Service Identifier SHALL produce a typed failure.

---

## 15.5 Alias Operation

The Alias Operation associates an alternate Service Identifier with an existing canonical Service Identifier.

An Alias SHALL NOT contain an implementation strategy.

An Alias SHALL NOT define an independent Lifetime Policy.

An Alias SHALL inherit the composition behavior of its canonical Binding.

Alias registration SHALL complete only after the complete alias graph has been validated.

---

# 16. Replacement Rules

## RP-001 — Explicit Replacement

**Statement**

An existing Binding SHALL be replaced only through an explicit Replace Operation.

**Rationale**

Prevents accidental modification of active composition rules.

**Related Invariants**

* INV-001
* INV-003
* INV-017

**Verification**

A registration attempt using an existing canonical Service Identifier is rejected unless replacement was explicitly requested.

---

## RP-002 — Atomic Replacement

**Statement**

Replacement SHALL atomically exchange the active Binding.

**Rationale**

Ensures that observers never encounter an intermediate or missing registration state.

**Related Invariants**

* INV-001
* INV-003
* INV-015

**Verification**

Failure during validation or replacement preserves the previous Binding unchanged.

---

## RP-003 — Replacement Immutability

**Statement**

Replacement SHALL create a new Binding and SHALL NOT mutate the previous Binding.

**Rationale**

Preserves immutable registration history and deterministic metadata.

**Related Invariants**

* INV-003

**Verification**

The original Binding remains observably unchanged after replacement.

---

## RP-004 — Shared Instance Invalidation

**Statement**

Replacing a shared Binding SHALL invalidate any instance associated with the previous Binding.

**Rationale**

Prevents reuse of an instance created under obsolete composition metadata.

**Related Invariants**

* INV-008

**Verification**

The first resolution after replacement creates or obtains an instance according to the replacement Binding.

---

# 17. Removal Rules

## RM-001 — Explicit Removal

**Statement**

A Binding SHALL be removed only through an explicit Remove Operation.

**Rationale**

Prevents implicit mutation of Aggregate state.

**Related Invariants**

* INV-017
* INV-018

---

## RM-002 — Complete Removal

**Statement**

Successful removal SHALL eliminate the Binding and all Container-owned state associated with it.

**Rationale**

Prevents obsolete registrations and shared instances from remaining reachable.

**Affected State**

* Binding
* Alias relationships
* Shared instance state
* Resolution status

---

## RM-003 — Alias Cleanup

**Statement**

Removing a canonical Binding SHALL remove or invalidate every Alias that resolves to it.

**Rationale**

Prevents dangling alias graphs.

**Related Invariants**

* INV-004
* INV-006

---

## RM-004 — Unknown Registration Failure

**Statement**

Removing an unknown Service Identifier SHALL fail using a typed exception.

**Rationale**

Avoids silent inconsistencies and programming errors.

---

# 18. Alias Rules

## AR-001 — Existing Canonical Target

**Statement**

An Alias SHALL reference a canonical Service Identifier associated with an active Binding.

**Rationale**

Prevents dangling alias registrations.

**Related Invariants**

* INV-004

---

## AR-002 — Distinct Identifiers

**Statement**

The Alias Service Identifier and the canonical Service Identifier SHALL be different.

**Rationale**

Prevents direct self-reference.

**Related Invariants**

* INV-005

---

## AR-003 — Acyclic Alias Graph

**Statement**

Alias registration SHALL be rejected when it introduces a cycle.

**Rationale**

Guarantees deterministic canonical resolution.

**Related Invariants**

* INV-006
* INV-014

---

## AR-004 — No Canonical Shadowing

**Statement**

An Alias SHALL NOT use a Service Identifier already owned by an active canonical Binding.

**Rationale**

Preserves unambiguous registration identity.

**Related Invariants**

* INV-001

---

## AR-005 — Alias Chain Support

**Statement**

Alias chains MAY contain more than one Alias, provided that the chain terminates at exactly one canonical Binding and remains acyclic.

**Rationale**

Supports controlled composition indirection without ambiguity.

---

## AR-006 — Canonical Behavior Inheritance

**Statement**

An Alias SHALL inherit the Binding Strategy and Lifetime Policy of its canonical Binding.

**Rationale**

Ensures that aliases introduce indirection only and do not create independent composition rules.

---

# 19. Validation Pipeline

## 19.1 Overview

Every Registration Operation SHALL pass through the Validation Pipeline before changing Aggregate state.

The Validation Pipeline SHALL be deterministic and free from observable side effects.

Validation SHALL NOT:

* instantiate Services;
* invoke factories;
* resolve dependencies;
* mutate Runtime;
* mutate Application;
* modify active registrations.

---

## 19.2 Validation Stages

Validation SHALL occur in the following order:

1. Operation validation
2. Service Identifier validation
3. Binding Strategy validation
4. Lifetime Policy validation
5. Metadata validation
6. Duplicate or replacement validation
7. Alias graph validation
8. Aggregate invariant validation

Failure at any stage SHALL stop the pipeline immediately.

---

## VR-001 — Complete Input

**Statement**

A Registration Operation SHALL provide every mandatory element required by its operation type.

**Rationale**

Prevents incomplete Binding definitions.

---

## VR-002 — Identifier Validity

**Statement**

Every Service Identifier SHALL satisfy the canonical identifier rules established by the Domain Model and public contracts.

**Rationale**

Ensures stable and unambiguous identity.

---

## VR-003 — Strategy Compatibility

**Statement**

The selected Binding Strategy SHALL be compatible with the supplied implementation data.

**Rationale**

Prevents invalid combinations of strategy and implementation.

---

## VR-004 — Lifetime Compatibility

**Statement**

The selected Lifetime Policy SHALL be compatible with the Binding Strategy.

**Rationale**

Prevents undefined runtime ownership behavior.

---

## VR-005 — Instance Lifetime

**Statement**

An Instance Binding SHALL behave as an already materialized shared instance and SHALL NOT use the Transient Lifetime Policy.

**Rationale**

An existing object cannot be recreated for each resolution.

---

## VR-006 — Alias Lifetime Prohibition

**Statement**

An Alias SHALL NOT define an independent Lifetime Policy.

**Rationale**

Alias behavior is inherited from the canonical Binding.

---

## VR-007 — Validation Atomicity

**Statement**

Validation failure SHALL leave the Container Aggregate unchanged.

**Rationale**

Preserves registration consistency.

**Related Invariants**

* INV-001
* INV-003
* INV-015

---

# 20. Binding State Machine

## 20.1 States

A Binding SHALL exist in exactly one of the following conceptual states:

| State      | Description                                                        |
| ---------- | ------------------------------------------------------------------ |
| Proposed   | A complete registration request exists but has not been validated. |
| Validated  | The request satisfies all validation rules.                        |
| Registered | The Binding is active within the Container Aggregate.              |
| Replaced   | The Binding has been superseded by another Binding.                |
| Removed    | The Binding no longer belongs to the Aggregate.                    |
| Rejected   | Validation failed and the Binding never became active.             |

---

## 20.2 Valid Transitions

```text
Proposed
   │
   ├── validation succeeds ──► Validated
   │                              │
   │                              └── registration succeeds ──► Registered
   │
   └── validation fails ─────► Rejected

Registered
   │
   ├── explicit replacement ─► Replaced
   │
   └── explicit removal ─────► Removed
```

No other state transition is valid.

---

## 20.3 Terminal States

The following states are terminal:

* Replaced
* Removed
* Rejected

A Binding in a terminal state SHALL NOT become Registered again.

A new registration SHALL create a new Binding definition.

---

## 20.4 State Visibility

Only Registered Bindings SHALL be available for dependency resolution.

Proposed and Validated states SHALL remain internal to the Registration Operation.

Rejected, Replaced and Removed Bindings SHALL NOT participate in new resolution operations.

---

# 21. Failure Model

## 21.1 General Rule

Every failed Registration Operation SHALL produce a typed exception.

Returning an error code is prohibited.

Returning `null` as a failure result is prohibited.

Silent correction is prohibited.

---

## 21.2 Failure Categories

| Category            | Description                                                          |
| ------------------- | -------------------------------------------------------------------- |
| Invalid Binding     | The Binding definition violates structural or compatibility rules.   |
| Duplicate Binding   | A Register Operation conflicts with an active canonical Binding.     |
| Invalid Replacement | A Replace Operation cannot safely replace the target Binding.        |
| Unknown Binding     | A Remove or Replace Operation targets an unknown Service Identifier. |
| Invalid Alias       | Alias registration violates target, identity or graph rules.         |
| Invariant Violation | The operation would leave the Aggregate inconsistent.                |

---

## FM-001 — No Partial State

**Statement**

A failed Registration Operation SHALL NOT expose partial Aggregate state.

**Rationale**

Preserves atomicity and deterministic behavior.

---

## FM-002 — Typed Failure

**Statement**

Every registration failure SHALL be represented by a typed Container exception.

**Rationale**

Provides stable diagnostics and programmatic failure handling.

---

## FM-003 — Cause Preservation

**Statement**

When a registration failure originates from another throwable condition, the typed Container exception SHOULD preserve the original cause.

**Rationale**

Supports diagnostics without weakening the public exception hierarchy.

---

## FM-004 — Safe Diagnostics

**Statement**

Failure diagnostics SHALL NOT expose secrets, object contents or unrelated Runtime state.

**Rationale**

Prevents sensitive-data leakage.

---

## FM-005 — Deterministic Failure

**Statement**

Equivalent invalid Registration Operations against equivalent Aggregate state SHALL produce equivalent typed failures.

**Rationale**

Supports reproducible testing and diagnosis.

---

# 22. Additional Registration Rules

## RR-007 — Registration Does Not Resolve

Registration SHALL NOT perform dependency resolution.

---

## RR-008 — Registration Does Not Instantiate

Registration SHALL NOT instantiate implementation types or invoke factories.

---

## RR-009 — One Aggregate

Every Binding SHALL belong to exactly one Container Aggregate.

---

## RR-010 — Explicit Strategy

Every non-Alias Binding SHALL define exactly one Binding Strategy.

---

## RR-011 — Explicit Lifetime

Every non-Alias Binding SHALL define exactly one Lifetime Policy.

---

## RR-012 — Canonical Identity Stability

The canonical Service Identifier of a registered Binding SHALL remain unchanged throughout that Binding's lifecycle.

---

## RR-013 — Replacement by New Definition

Replacement SHALL create a new Binding definition.

---

## RR-014 — Removal Finality

A Removed Binding SHALL NOT become active again.

---

## RR-015 — Alias Indirection Only

An Alias SHALL introduce only identifier indirection and SHALL NOT own independent implementation data.

---

## RR-016 — Validation Is Side-Effect Free

Validation SHALL NOT mutate Aggregate or Runtime state.

---

## RR-017 — Registration Ordering

When multiple registrations are applied sequentially, their observable result SHALL reflect the explicit operation order.

---

## RR-018 — Container Isolation

Registration in one Container SHALL NOT affect any other Container.

---

## RR-019 — Metadata Stability

Registration metadata SHALL remain stable after successful registration.

---

## RR-020 — Failure Rollback

A failed operation SHALL preserve the exact active registration state that existed before the operation began.

---

# Revision History

| Version | Date       | Description                                                                                                                                |
| ------- | ---------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| 0.1.0   | 2026-07-16 | Initial purpose, scope, lifecycle and registration invariants.                                                                             |
| 0.2.0   | 2026-07-16 | Added Binding Model, Binding Strategies, Lifetime Policies, registration metadata and RR-001 through RR-006.                               |
| 0.3.0   | 2026-07-16 | Added registration operations, replacement, removal, aliases, validation pipeline, state machine, failure model and RR-007 through RR-020. |

# 23. Rule Index

This section provides the canonical index of every normative rule defined by this specification.

Rule identifiers are permanent. Existing identifiers SHALL NOT be renumbered or reused.

## 23.1 Registration Rules

| Rule | Title |
|---|---|
| RR-001 | Canonical Registration |
| RR-002 | Unique Service Identifier |
| RR-003 | Atomic Registration |
| RR-004 | Immutable Binding Definition |
| RR-005 | Validation Before Registration |
| RR-006 | Deterministic Metadata |
| RR-007 | Registration Does Not Resolve |
| RR-008 | Registration Does Not Instantiate |
| RR-009 | One Aggregate |
| RR-010 | Explicit Strategy |
| RR-011 | Explicit Lifetime |
| RR-012 | Canonical Identity Stability |
| RR-013 | Replacement by New Definition |
| RR-014 | Removal Finality |
| RR-015 | Alias Indirection Only |
| RR-016 | Validation Is Side-Effect Free |
| RR-017 | Registration Ordering |
| RR-018 | Container Isolation |
| RR-019 | Metadata Stability |
| RR-020 | Failure Rollback |

## 23.2 Replacement Rules

| Rule | Title |
|---|---|
| RP-001 | Explicit Replacement |
| RP-002 | Atomic Replacement |
| RP-003 | Replacement Immutability |
| RP-004 | Shared Instance Invalidation |

## 23.3 Removal Rules

| Rule | Title |
|---|---|
| RM-001 | Explicit Removal |
| RM-002 | Complete Removal |
| RM-003 | Alias Cleanup |
| RM-004 | Unknown Registration Failure |

## 23.4 Alias Rules

| Rule | Title |
|---|---|
| AR-001 | Existing Canonical Target |
| AR-002 | Distinct Identifiers |
| AR-003 | Acyclic Alias Graph |
| AR-004 | No Canonical Shadowing |
| AR-005 | Alias Chain Support |
| AR-006 | Canonical Behavior Inheritance |

## 23.5 Validation Rules

| Rule | Title |
|---|---|
| VR-001 | Complete Input |
| VR-002 | Identifier Validity |
| VR-003 | Strategy Compatibility |
| VR-004 | Lifetime Compatibility |
| VR-005 | Instance Lifetime |
| VR-006 | Alias Lifetime Prohibition |
| VR-007 | Validation Atomicity |

## 23.6 Failure Rules

| Rule | Title |
|---|---|
| FM-001 | No Partial State |
| FM-002 | Typed Failure |
| FM-003 | Cause Preservation |
| FM-004 | Safe Diagnostics |
| FM-005 | Deterministic Failure |

---

# 24. Traceability Matrix

## 24.1 Purpose

Every normative rule SHALL be traceable to:

- one or more Domain Invariants;
- one or more public or internal contracts;
- one planned implementation responsibility;
- one or more automated verification scenarios.

Implementation reports SHALL reference rule identifiers rather than relying only on descriptive text.

## 24.2 Registration Traceability

| Rules | Related Invariants | Planned Responsibility | Required Verification |
|---|---|---|---|
| RR-001, RR-002 | INV-001, INV-002 | Canonical registration management | Unique identifier and duplicate registration tests |
| RR-003, RR-020 | INV-001, INV-003, INV-015 | Atomic Aggregate mutation | Failure rollback and no-partial-state tests |
| RR-004, RR-013, RR-019 | INV-003 | Immutable Binding management | Immutability and replacement identity tests |
| RR-005, RR-016 | INV-017, INV-020 | Registration validation | Validation ordering and side-effect tests |
| RR-006, RR-017 | INV-007 | Deterministic registration processing | Equivalent input and ordered registration tests |
| RR-007, RR-008 | INV-017, INV-018 | Registration boundary enforcement | No resolution, construction or factory invocation tests |
| RR-009, RR-018 | INV-015, INV-016 | Container ownership and isolation | Independent Container tests |
| RR-010, RR-011 | INV-017 | Binding completeness validation | Missing strategy and lifetime tests |
| RR-012 | INV-002, INV-003 | Canonical identity preservation | Identity stability tests |
| RR-014 | INV-003 | Terminal Binding-state enforcement | Removed Binding reactivation rejection tests |
| RR-015 | INV-004, INV-005, INV-006 | Alias registration | Alias contains no independent composition-data tests |

## 24.3 Replacement Traceability

| Rules | Related Invariants | Planned Responsibility | Required Verification |
|---|---|---|---|
| RP-001 | INV-001, INV-003, INV-017 | Explicit replacement API | Duplicate register versus explicit replace tests |
| RP-002 | INV-001, INV-003, INV-015 | Atomic replacement transaction | Replacement rollback tests |
| RP-003 | INV-003 | Immutable Binding replacement | Original Binding preservation tests |
| RP-004 | INV-008 | Shared-instance invalidation | Replacement invalidates prior shared instance tests |

## 24.4 Removal Traceability

| Rules | Related Invariants | Planned Responsibility | Required Verification |
|---|---|---|---|
| RM-001 | INV-017, INV-018 | Explicit removal API | No implicit removal tests |
| RM-002 | INV-003, INV-015 | Registration-state cleanup | Binding, resolved state and shared-instance cleanup tests |
| RM-003 | INV-004, INV-006 | Alias cleanup | Direct and chained alias cleanup tests |
| RM-004 | INV-007 | Typed unknown-service failure | Unknown removal tests |

## 24.5 Alias Traceability

| Rules | Related Invariants | Planned Responsibility | Required Verification |
|---|---|---|---|
| AR-001 | INV-004 | Canonical-target validation | Missing target rejection tests |
| AR-002 | INV-005 | Self-reference validation | Direct self-alias rejection tests |
| AR-003 | INV-006, INV-014 | Alias graph validation | Direct and indirect cycle tests |
| AR-004 | INV-001 | Identifier collision validation | Canonical shadowing rejection tests |
| AR-005 | INV-006 | Alias-chain traversal | Valid multi-level alias tests |
| AR-006 | INV-004 | Canonical behavior inheritance | Strategy and lifetime inheritance tests |

## 24.6 Validation Traceability

| Rules | Related Invariants | Planned Responsibility | Required Verification |
|---|---|---|---|
| VR-001 | INV-017 | Registration-input validation | Missing mandatory element tests |
| VR-002 | INV-002 | Service-Identifier validation | Empty, malformed and whitespace identifier tests |
| VR-003 | INV-017 | Strategy compatibility validation | Invalid strategy-data combinations |
| VR-004 | INV-008, INV-009, INV-010 | Lifetime compatibility validation | Invalid strategy-lifetime combinations |
| VR-005 | INV-010 | Existing-instance validation | Transient existing-instance rejection |
| VR-006 | INV-004 | Alias validation | Independent alias-lifetime rejection |
| VR-007 | INV-001, INV-003, INV-015 | Validation transaction | Aggregate remains unchanged after validation failure |

## 24.7 Failure Traceability

| Rules | Related Invariants | Planned Responsibility | Required Verification |
|---|---|---|---|
| FM-001 | INV-001, INV-003, INV-015 | Transaction rollback | No partial mutation tests |
| FM-002 | INV-007 | Exception hierarchy | Typed exception tests for every failure category |
| FM-003 | INV-007 | Exception wrapping | Original cause preservation tests |
| FM-004 | INV-019, INV-020 | Safe diagnostics | Secret, object-state and stack-detail exclusion tests |
| FM-005 | INV-007 | Deterministic error mapping | Equivalent invalid operations produce equivalent failures |

---

# 25. Implementation Checklist

An implementation SHALL NOT claim conformance until every mandatory checklist item is complete.

## 25.1 Binding Model

- [ ] Every Binding has exactly one canonical Service Identifier.
- [ ] Binding definitions become immutable after registration.
- [ ] Binding Strategy and Lifetime Policy are represented independently.
- [ ] Alias registrations contain no independent implementation data.
- [ ] Registration metadata remains stable after successful registration.

## 25.2 Registration

- [ ] Register Operation is explicit.
- [ ] Register Operation rejects duplicate canonical identifiers.
- [ ] Registration is atomic.
- [ ] Registration does not resolve dependencies.
- [ ] Registration does not instantiate implementation types.
- [ ] Registration does not invoke factories.
- [ ] Registration order is deterministic.
- [ ] Registrations are isolated per Container.

## 25.3 Replacement

- [ ] Replacement is explicit.
- [ ] Replacement creates a new immutable Binding.
- [ ] Replacement preserves the canonical Service Identifier.
- [ ] Failed replacement preserves the previous Binding.
- [ ] Replacement invalidates shared state belonging to the previous Binding.

## 25.4 Removal

- [ ] Removal is explicit.
- [ ] Removal deletes Container-owned registration state.
- [ ] Removal invalidates direct and indirect aliases.
- [ ] Removal clears shared-instance and resolved-state information.
- [ ] Removing an unknown identifier produces a typed failure.
- [ ] Removed Bindings cannot be reactivated.

## 25.5 Aliases

- [ ] Alias target exists.
- [ ] Alias and target identifiers differ.
- [ ] Alias graphs are acyclic.
- [ ] Aliases cannot shadow canonical Bindings.
- [ ] Alias chains terminate at one canonical Binding.
- [ ] Aliases inherit canonical strategy and lifetime behavior.

## 25.6 Validation

- [ ] Validation precedes every Aggregate mutation.
- [ ] Validation follows the specified stage order.
- [ ] Validation is deterministic.
- [ ] Validation has no observable side effects.
- [ ] Invalid Binding Strategy combinations are rejected.
- [ ] Invalid Lifetime Policy combinations are rejected.
- [ ] Validation failure preserves the exact previous Aggregate state.

## 25.7 Failure Handling

- [ ] Every failure uses a typed Container exception.
- [ ] Original causes are preserved when applicable.
- [ ] Diagnostics exclude sensitive and unrelated Runtime state.
- [ ] Equivalent failures map deterministically.
- [ ] No failure returns `null` or an error code.
- [ ] No failed operation leaves partial state.

## 25.8 Verification and Quality

- [ ] Every normative rule has automated verification.
- [ ] Every relevant Domain Invariant has test coverage.
- [ ] PHPStan level 8 passes with no baseline or suppressions.
- [ ] PHP-CS-Fixer dry-run passes.
- [ ] Composer validation passes.
- [ ] Existing WP-002 and WP-003 regression tests pass.
- [ ] Implementation report contains rule-level traceability.

---

# 26. Conformance

## 26.1 Conformant Implementation

An implementation MAY declare conformance with this specification only when:

1. every mandatory normative rule is implemented;
2. every listed Domain Invariant is preserved;
3. every Registration Operation follows the specified state model;
4. every mandatory checklist item is complete;
5. automated verification demonstrates compliance;
6. no undocumented registration behavior is exposed as public API.

The conformance declaration SHALL identify:

- specification identifier;
- specification version;
- implementation version;
- verification environment;
- Quality Gate result.

## 26.2 Partial Implementation

A partial implementation SHALL NOT identify itself as conformant.

It MAY be identified as experimental when:

- its incomplete areas are explicitly documented;
- it does not claim stable compatibility;
- it is not used as the production Runtime Composition Engine.

## 26.3 Extensions

Extensions MAY introduce additional internal behavior provided that they:

- preserve every normative rule;
- preserve every Domain Invariant;
- do not alter compatibility-protected public contracts;
- do not change observable registration semantics;
- remain explicitly documented.

---

# 27. Compatibility

The following observable behavior is compatibility protected after the first stable release:

- canonical identifier uniqueness;
- Binding immutability;
- atomic registration;
- explicit replacement;
- explicit removal;
- Alias indirection semantics;
- validation-before-mutation;
- typed failure behavior;
- deterministic operation ordering;
- Container isolation.

A breaking change to any compatibility-protected behavior requires:

- an approved architectural decision;
- an updated specification version;
- a documented migration strategy;
- semantic-version review.

Internal storage, indexing and optimization strategies are not compatibility protected when observable behavior remains unchanged.

---

# 28. Change Impact

Changing this specification may affect:

- public Container contracts;
- Binding and Alias domain models;
- registration validation;
- replacement and removal semantics;
- shared-instance invalidation;
- exception hierarchy;
- Resolution Engine assumptions;
- Runtime integration;
- PHPUnit verification;
- SIF Builder traceability generation;
- component metadata;
- implementation reports.

Any change affecting Domain concepts or architectural boundaries requires an approved ADR before this document is updated.

Editorial clarification that does not alter observable behavior MAY be incorporated through a patch revision.

---

# 29. Implementation Notes

This section is informative.

Implementations may use arrays, maps, collections or dedicated internal repositories to store registration state.

Storage mechanisms remain internal and SHALL NOT alter observable behavior.

Registration transactions need not use a database transaction. Atomicity refers to the logical consistency of the Container Aggregate.

A copy-on-write or validate-before-commit implementation may be used to preserve rollback guarantees.

Alias validation may traverse the complete prospective graph before committing the Alias.

Shared-instance invalidation belongs to the Registration Model, while creation and reuse of shared instances are specified by the Resolution Engine.

The `Scoped` Lifetime Policy remains declarative in this chapter. Scope creation and ownership require a separate approved specification before becoming operational.

---

# 30. Acceptance Criteria

This specification is ready for Release Candidate status when:

- every Registration Operation is defined;
- every Binding Strategy is defined;
- every Lifetime Policy is defined;
- validation order is deterministic;
- Binding states and transitions are complete;
- replacement and removal semantics are unambiguous;
- Alias behavior is complete;
- failure behavior is typed and deterministic;
- Rule Index is complete;
- traceability covers every normative rule;
- implementation checklist is complete;
- conformance and compatibility requirements are defined;
- no unresolved architectural decision remains.

---

# 31. Revision History

| Version | Date | Status | Description |
|---|---|---|---|
| 0.1.0 | 2026-07-16 | Approved | Initial purpose, scope, lifecycle and registration invariants. |
| 0.2.0 | 2026-07-16 | Approved | Added Binding Model, Binding Strategies, Lifetime Policies, registration metadata and RR-001 through RR-006. |
| 0.3.0 | 2026-07-16 | Approved | Added registration operations, replacement, removal, Alias rules, validation pipeline, state machine, failure model and RR-007 through RR-020. |
| 0.4.0 | 2026-07-16 | Draft for Review | Added Rule Index, Traceability Matrix, Implementation Checklist, Conformance, Compatibility, Change Impact, Implementation Notes and final acceptance criteria. |

---

# End of Specification

