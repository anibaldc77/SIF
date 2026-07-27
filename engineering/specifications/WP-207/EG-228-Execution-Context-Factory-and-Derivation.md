---
id: EG-228
title: Execution Context Factory and Derivation
summary: Defines deterministic root context creation and immutable parent-child derivation through injected identifier and clock contracts.
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
  - factory
  - derivation
  - deterministic-testing
depends_on:
  - EG-227
  - EG-226
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-228 — Execution Context Factory and Derivation

## 1. Purpose

This specification defines explicit and deterministic creation of root execution contexts and immutable derivation of child contexts.

## 2. Public contracts

The increment SHALL provide:

- `ContextIdGeneratorInterface`;
- `ClockInterface`;
- `ExecutionContextFactoryInterface`;
- `ExecutionContextFactory`.

The factory SHALL receive its identifier generator and clock through constructor injection and SHALL NOT use static state, global registries, random functions, or the system clock directly.

## 3. Root creation

`createRoot()` SHALL:

- generate exactly one context identifier;
- use that same object as the root correlation identifier;
- obtain `createdAt` from the injected clock;
- preserve supplied attributes and optional metadata;
- leave causation and parent identifiers absent.

## 4. Child derivation

`derive()` SHALL:

- generate a new context identifier;
- preserve the parent correlation identifier;
- set `parentContextId` to the parent context identifier;
- accept an optional explicit causation identifier;
- obtain a new creation instant from the injected clock;
- inherit actor, tenant, locale, and timezone;
- inherit operation and source unless explicit replacements are supplied;
- merge attributes without mutating the parent context.

## 5. Attribute merge policy

`ContextAttributes::merged()` SHALL return an immutable collection.

Incoming top-level keys SHALL replace existing keys. Keys absent from the incoming collection SHALL remain unchanged. Nested values SHALL remain atomic at the top-level merge boundary; recursive deep merge is outside this increment.

Merging an empty collection MAY return the original immutable instance.

## 6. Determinism

Identifier and time production SHALL be replaceable in tests. Test fixtures SHALL demonstrate reproducible identifiers, timestamps, correlation, causation, parent assignment, and attribute results.

## 7. Exclusions

This increment SHALL NOT include:

- concrete production UUID or ULID generation;
- a system clock implementation;
- serialization or redaction;
- ambient/static context access;
- Runtime, event, observation, or audit integration;
- persistence or transport adapters.

## 8. Acceptance criteria

The increment is accepted when root creation, child derivation, metadata inheritance, correlation preservation, parent and causation assignment, immutable attribute merging, deterministic test generation, PHPStan level 8, Builder validation, and the full repository quality gate pass.
