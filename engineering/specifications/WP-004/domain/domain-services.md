# WP-004 — Domain Services

**Specification:** SPEC-WP-004-DI-CONTAINER

**Document:** Domain Services

**Status:** Approved

**Version:** 1.0.0

---

# 1. Purpose

This document defines the Domain Services of the Dependency Injection Container.

Domain Services encapsulate business behavior that does not naturally belong to an Entity or Value Object.

They SHALL implement domain rules while remaining independent from infrastructure concerns.

---

# 2. Design Principles

Every Domain Service SHALL satisfy the following principles.

## DS-001 — Stateless

Domain Services SHALL NOT own persistent state.

All state SHALL remain inside the Aggregate Root.

---

## DS-002 — Deterministic

Given identical inputs and Aggregate state, every operation SHALL produce identical observable behavior.

---

## DS-003 — Infrastructure Independent

Domain Services SHALL NOT depend directly upon:

- repositories;
- caches;
- storage engines;
- framework infrastructure.

---

## DS-004 — Single Responsibility

Each Domain Service SHALL implement one cohesive domain responsibility.

---

# 3. Domain Services

WP-004 defines four Domain Services.

```
ResolutionEngine

BindingValidator

AliasResolver

FactoryResolver
```

No additional Domain Service exists in WP-004.

---

# 4. ResolutionEngine

## Responsibility

Executes the dependency resolution algorithm.

The ResolutionEngine is responsible for transforming a ServiceIdentifier into one resolved Service instance while preserving every Aggregate invariant.

---

## Responsibilities

- resolve Bindings;
- execute Factory resolution;
- resolve Aliases;
- detect circular dependencies;
- coordinate ResolutionContext;
- produce typed failures.

---

## Pre-conditions

- ServiceIdentifier exists;
- Aggregate is consistent.

---

## Post-conditions

Exactly one of the following SHALL occur:

- one Service instance is returned;
- one typed exception is thrown.

---

## Preserved Invariants

- INV-007
- INV-008
- INV-009
- INV-010
- INV-012
- INV-013
- INV-014

---

# 5. BindingValidator

## Responsibility

Validates registrations before they become part of the Aggregate.

---

## Responsibilities

- validate identifiers;
- validate lifetime;
- validate aliases;
- reject invalid registrations.

---

## Pre-conditions

A registration request exists.

---

## Post-conditions

Registration is either:

- accepted;

or

- rejected.

No partial validation exists.

---

## Preserved Invariants

- INV-001
- INV-002
- INV-003
- INV-004
- INV-005
- INV-006
- INV-017

---

# 6. AliasResolver

## Responsibility

Resolves Alias chains into canonical ServiceIdentifiers.

---

## Responsibilities

- resolve aliases;
- detect alias loops;
- normalize traversal.

---

## Pre-conditions

Alias exists.

---

## Post-conditions

Exactly one canonical ServiceIdentifier is produced.

---

## Preserved Invariants

- INV-004
- INV-005
- INV-006

---

# 7. FactoryResolver

## Responsibility

Creates Service instances through registered Factory strategies.

---

## Responsibilities

- invoke Factories;
- validate returned instances;
- enforce lifetime rules.

---

## Pre-conditions

A Factory Binding exists.

---

## Post-conditions

Exactly one valid Service instance is returned.

---

## Preserved Invariants

- INV-008
- INV-009
- INV-010
- INV-011

---

# 8. Collaboration Model

```
Container
      │
      ├────────► BindingValidator
      │
      ├────────► AliasResolver
      │
      ├────────► ResolutionEngine
      │
      └────────► FactoryResolver
```

The Container coordinates Domain Services.

Domain Services SHALL NOT invoke one another directly unless explicitly specified by the Resolution algorithm.

---

# 9. Error Handling

Domain Services SHALL fail using typed exceptions.

Returning error codes is prohibited.

Returning null for failures is prohibited.

Silent recovery is prohibited.

---

# 10. Lifecycle

Domain Services are ephemeral.

They MAY be instantiated by the Container implementation.

They SHALL NOT preserve state between operations.

---

# 11. Future Extension

Future Work Packages MAY introduce additional Domain Services.

Existing responsibilities SHALL NOT be reassigned in incompatible ways.

---

# 12. Traceability

Every Domain Service SHALL be referenced by:

- implementation;
- PHPUnit tests;
- implementation report;
- quality gate.

---

# 13. Acceptance Criteria

This document is complete when:

- every Domain Service has a single responsibility;
- responsibilities do not overlap;
- preserved invariants are identified;
- lifecycle and collaboration rules are defined.

---

# End of Document