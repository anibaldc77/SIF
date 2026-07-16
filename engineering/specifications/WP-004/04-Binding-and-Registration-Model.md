# WP-004 — Binding and Registration Model

**Document ID:** WP-004-04

**Work Package:** WP-004 — Runtime Composition Engine

**Version:** 0.1.0

**Status:** Draft for Review

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
