---
id: EG-291
title: Deterministic Resource Registry
summary: Defines explicit resource registration, exact duplicate detection, monotonic registration order and immutable compiled registry snapshots.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-29
updated: 2026-07-29
work_package: WP-215
tags:
  - foundation
  - resources
  - registry
  - determinism
  - immutability
depends_on:
  - EG-289
  - EG-290
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-291 — Deterministic Resource Registry

## 1. Purpose

WP-215-I3 introduces explicit, deterministic registration over the immutable resource model established by EG-290.

This increment SHALL NOT access the filesystem, resolve authorized roots, publish resources, perform module discovery or integrate with runtime bootstrap.

## 2. Contracts

The subsystem SHALL expose:

- a read-only `ResourceRegistryInterface`;
- a mutable `MutableResourceRegistryInterface`;
- a mutable `ResourceRegistry` used during composition;
- an immutable `CompiledResourceRegistry` used after composition;
- a `RegisteredResource` value carrying the descriptor and its registration order.

## 3. Explicit registration

Resources SHALL enter the registry only through an explicit `register()` operation.

Registration SHALL:

- preserve the original immutable descriptor;
- assign a zero-based, monotonically increasing registration order;
- return the resulting `RegisteredResource`;
- reject an exact duplicate qualified identifier.

No replacement or override SHALL occur silently.

## 4. Identity and duplicate rules

The registry key SHALL be:

```text
<namespace>:<identifier>
```

Identity SHALL be case-sensitive.

The same identifier MAY occur in different namespaces. The same namespace and identifier combination SHALL occur at most once in a registry snapshot, regardless of type, source or priority.

An exact duplicate SHALL raise `DuplicateResourceException`.

## 5. Lookup

Lookup SHALL require both an explicit namespace and identifier.

`has()` SHALL return whether the exact key exists.

`get()` SHALL return the original descriptor or raise `ResourceNotFoundException`.

This increment SHALL NOT implement fallback namespaces, wildcard lookup or type coercion.

## 6. Deterministic ordering

Registry enumeration SHALL be deterministic and SHALL order entries by:

1. higher `ResourcePriority` first;
2. lower registration order first when priorities are equal.

Registration order SHALL therefore provide a stable final tie-breaker without depending on hash-table behavior, filesystem order or locale.

## 7. Compilation

`compile()` SHALL create an immutable snapshot.

A compiled registry SHALL:

- preserve the already computed deterministic order;
- preserve exact lookup semantics;
- reject duplicate keys supplied to its constructor;
- remain unaffected by later registrations in the mutable registry;
- expose no mutation operation.

Compilation SHALL NOT read files, validate physical existence or generate publication output.

## 8. Typed failures

The increment SHALL add:

- `DuplicateResourceException`;
- `ResourceNotFoundException`;
- `InvalidRegistrationOrderException`.

All SHALL derive from `ResourceException`.

## 9. Compatibility

The increment is additive. Existing runtime, module, configuration, logging and error-handling APIs SHALL remain unchanged.

## 10. Exit criteria

WP-215-I3 is complete when:

- explicit registration and exact lookup are covered by tests;
- duplicate registration is rejected;
- namespace separation and case sensitivity are covered;
- deterministic priority and registration ordering are covered;
- compiled snapshots are immutable with respect to later mutable changes;
- PHPStan level 8 and repository governance validation succeed.
