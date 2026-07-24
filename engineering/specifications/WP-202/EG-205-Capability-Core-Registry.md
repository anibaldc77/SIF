---
id: EG-205
title: Capability Core Registry
summary: Defines the minimal capability identity and registry model used to expose optional Runtime services without coupling Runtime Core to infrastructure or business logic.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - runtime
  - capability
  - registry
  - extension
  - contracts
work_package: WP-202
depends_on:
  - EG-202
  - EG-204
  - ADR-0005
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-205 — Capability Core Registry

## 1. Objective

WP-202-I1 establishes the smallest infrastructure-independent model required to register and resolve optional Runtime capabilities.

The increment introduces capability identity and deterministic in-memory registration without modifying `Application`, `Kernel`, `Runtime`, `Lifecycle`, or their public contracts.

## 2. Architectural role

A capability represents an optional framework service or extension boundary that can be made available to an application Runtime. Examples may include configuration, events, logging, cache, persistence, queues, or adapter services, but this increment implements none of those services.

The registry SHALL remain a neutral container of capability instances. It SHALL NOT:

- create capabilities;
- control their lifecycle;
- resolve transitive dependencies;
- perform dependency injection;
- discover modules;
- load configuration;
- depend on external infrastructure;
- contain application or business rules.

## 3. Capability identity

Every capability SHALL implement `CapabilityInterface` and expose a stable identifier through `identifier()`.

An identifier:

- SHALL be a non-empty string after trimming surrounding whitespace;
- SHALL be unique inside one registry;
- SHALL remain case-sensitive;
- SHOULD be stable across executions and releases;
- SHOULD describe the capability role rather than its concrete implementation.

The registry owns boundary normalization. It trims surrounding whitespace but does not alter case or internal characters.

## 4. Registry invariants

`CapabilityRegistry` SHALL:

- preserve registration order;
- reject duplicate normalized identifiers;
- reject empty identifiers for registration and lookup;
- resolve a capability by its identifier;
- report whether an identifier is registered;
- return all capabilities in insertion order;
- support filtering by compatible class or interface;
- expose deterministic iteration keyed by normalized identifier;
- expose its current count.

Registration is explicit. Silent replacement is forbidden.

## 5. Failure model

The capability subsystem defines a hierarchy rooted at `CapabilityException`, itself derived from `FoundationException`.

Approved failures are:

- `InvalidCapabilityIdentifierException`;
- `DuplicateCapabilityException`;
- `CapabilityNotFoundException`.

Exception messages SHALL identify the violated invariant and, where applicable, the normalized identifier.

## 6. Compatibility

WP-202-I1 adds new classes only. It changes no existing constructor, interface, lifecycle flow, state transition, provider behavior, or Runtime result.

Integration with `Application` and `Kernel` requires a later approved increment. This separation prevents the registry model from forcing premature changes to Runtime Core contracts.

## 7. Acceptance criteria

- Capability identity is represented by a minimal public contract.
- Registry operations are deterministic and independently testable.
- Duplicate and invalid identifiers are rejected explicitly.
- Missing capability resolution produces a specific exception.
- Insertion order and keyed iteration are preserved.
- Runtime type filtering is covered by tests.
- Existing Runtime behavior remains unchanged.
- Composer validation succeeds.
- PHPUnit has no failures.
- PHPStan level 8 has no errors.
- Builder reports zero diagnostics after governed artifact regeneration.
