---
id: EG-227
title: Execution Context Core Contracts and Immutable Model
summary: Defines the initial immutable Execution Context contract, opaque identifiers, validated attributes, typed exceptions, and infrastructure-neutral core model.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-207
tags:
  - foundation
  - context
  - immutable-model
  - contracts
  - security
depends_on:
  - EG-226
  - EG-225
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-227 — Execution Context Core Contracts and Immutable Model

## 1. Purpose

This specification defines the first executable increment of the SIF Execution Context subsystem.

The increment introduces the read-only context contract, immutable identifier and attribute value objects, the immutable context implementation, and typed validation exceptions. It does not introduce factories, derivation, serialization, redaction, ambient state, or Runtime integration.

## 2. Delivered public model

The increment SHALL provide:

- `ExecutionContextInterface`;
- `ExecutionContext`;
- `ContextId`;
- `ContextAttributes`;
- `InvalidContextIdException`;
- `InvalidContextAttributeKeyException`;
- `UnsupportedContextAttributeValueException`.

All classes SHALL remain infrastructure-neutral and compatible with PHP 8.2 and PHPStan level 8.

## 3. ExecutionContextInterface

The contract SHALL expose read-only access to:

- context identifier;
- correlation identifier;
- optional causation identifier;
- optional parent context identifier;
- optional actor and tenant identifiers;
- optional operation, source, locale, and timezone hints;
- immutable creation timestamp;
- immutable context attributes.

The contract SHALL NOT expose mutators.

## 4. ContextId

`ContextId` SHALL be an opaque value object.

It SHALL:

- reject empty and whitespace-only identifiers;
- preserve the supplied non-empty value without interpretation;
- expose string conversion;
- support explicit value equality;
- make no assumptions about UUID, ULID, database, transport, or trust semantics.

## 5. ContextAttributes

`ContextAttributes` SHALL accept only deterministic JSON-compatible values:

- `null`;
- booleans;
- integers;
- finite floats;
- strings;
- nested arrays containing supported values.

Top-level keys SHALL be non-empty strings. Nested associative string keys SHALL also be non-empty. List integer keys SHALL be allowed.

The model SHALL reject:

- objects;
- resources;
- closures;
- non-finite floats;
- recursive arrays;
- unsupported PHP values.

The collection SHALL expose read-only query operations and SHALL return arrays by value.

## 6. ExecutionContext

`ExecutionContext` SHALL be a final immutable implementation of `ExecutionContextInterface`.

Construction SHALL require:

- `ContextId $contextId`;
- `ContextId $correlationId`;
- `DateTimeImmutable $createdAt`.

Attributes SHALL default to an empty immutable collection. All other standard fields SHALL be optional.

The model SHALL preserve object identity of supplied value objects and SHALL perform no external lookup, inference, authorization, persistence, or transport normalization.

## 7. Error model

Invalid input SHALL fail immediately through typed exceptions:

| Condition | Exception |
|---|---|
| Empty context identifier | `InvalidContextIdException` |
| Empty or non-string top-level key | `InvalidContextAttributeKeyException` |
| Empty nested associative key | `InvalidContextAttributeKeyException` |
| Unsupported, recursive, or non-finite value | `UnsupportedContextAttributeValueException` |

No validation failure SHALL be silently normalized.

## 8. Immutability boundary

The increment SHALL NOT provide mutation methods such as `set`, `add`, `remove`, or `replace`.

Derivation and copy-with operations are reserved for a later WP-207 increment and SHALL return new instances when introduced.

## 9. Security boundary

The core model validates representation, not confidentiality policy.

Callers remain responsible for excluding passwords, tokens, credentials, private keys, and other secrets. Redaction and safe canonical serialization are reserved for later increments.

## 10. Exclusions

This increment SHALL NOT include:

- `ContextFactoryInterface`;
- automatic identifier generation;
- child derivation;
- canonical serialization;
- redaction policy;
- Runtime, event, audit, HTTP, CLI, job, or session integration;
- static or ambient context storage.

## 11. Acceptance criteria

The increment is accepted when:

1. the contract is read-only;
2. identifiers reject empty values;
3. supported nested attributes are accepted;
4. invalid keys and values fail through typed exceptions;
5. the context exposes all supplied values without mutation;
6. optional values remain explicitly nullable;
7. the existing Runtime baseline remains unchanged;
8. PHPUnit, PHPStan, Builder validation, and deterministic generation pass.
