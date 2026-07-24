---
id: ADR-0005
title: Adopt a Capability-Driven Runtime
summary: Establishes capabilities as the stable abstraction through which the Runtime accesses replaceable framework services.
status: Approved
version: 1.0.0
category: Architecture Decision Record
document_class: GovernanceDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - runtime
  - capabilities
  - architecture
  - dependency-injection
work_package: WP-200
depends_on:
  - EG-200
related_adrs:
  - ADR-0004
supersedes: null
superseded_by: null
---

# ADR-0005 — Adopt a Capability-Driven Runtime

## Status

Accepted.

## Context

SIF Runtime must coordinate replaceable infrastructure services without depending on concrete implementations, optional packages, or business modules. Direct access to concrete classes, or using the dependency injection container as the public framework entry point, would expose implementation details and make module replacement harder.

## Decision

The Runtime SHALL be capability-driven.

A capability is a stable, named runtime contract that represents a service the framework can provide, such as configuration, logging, events, cache, clock, filesystem, translation, mail, queue, scheduling, cryptography, identifiers, or HTTP transport.

The Runtime SHALL expose capabilities through a dedicated `CapabilityRegistry`. The registry SHALL mediate capability registration, selection, replacement, decoration, and resolution. The dependency injection container SHALL remain responsible for object construction and dependency resolution, but SHALL NOT become the public architectural entry point of the framework.

Conceptually:

```text
Application
    -> Runtime
        -> Capability Registry
            -> Container
                -> Provider implementation
```

## Invariants

1. Capability identity is independent of implementation class names.
2. Runtime Core depends only on capability contracts and Runtime contracts.
3. Modules may provide capabilities but may not modify Runtime internals.
4. A required capability must fail deterministically when unavailable.
5. Optional capabilities must be queryable without causing failure.
6. Selection among multiple providers must be deterministic.
7. Replacements and decorators must remain observable and auditable.
8. The registry must not become a second general-purpose service container.
9. Capability resolution must not hide circular dependencies.
10. Public capability identifiers are governed by SemVer.

## Consequences

### Positive

- replaceable infrastructure implementations;
- reduced coupling between Runtime and optional subsystems;
- test doubles can replace capabilities explicitly;
- modules can extend the framework without changing Core;
- environment-specific providers become straightforward;
- public APIs can remain stable while implementations evolve.

### Costs

- an additional abstraction and registry are required;
- capability identity and resolution policy must be governed;
- diagnostics are needed for ambiguity, absence, cycles, and invalid decoration;
- indiscriminate capability creation could produce a service-locator anti-pattern.

## Rejected alternatives

### Container as the public entry point

Rejected because it exposes implementation-level service identifiers and encourages arbitrary service location.

### Concrete Runtime dependencies

Rejected because concrete logging, cache, event, or configuration implementations would become coupled to Core.

### Static global facade

Rejected because it obscures lifecycle, complicates testing, and weakens context isolation.

## Follow-up work

- WP-201: Dependency Injection integration boundaries.
- WP-202: Capability Registry and resolution model.
- WP-203: Configuration capability.
- WP-204: Module and provider publication model.
- WP-205: Runtime Context.
- WP-206: Event capability and dispatcher.
