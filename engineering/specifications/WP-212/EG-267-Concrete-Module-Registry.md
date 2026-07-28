---
id: EG-267
title: Concrete Module Registry
summary: Defines deterministic module registration, duplicate rejection, ordered inspection, and irreversible freeze semantics.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-28
updated: 2026-07-28
work_package: WP-212
tags:
  - foundation
  - modules
  - registry
  - lifecycle
depends_on:
  - EG-265
  - EG-266
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-267 — Concrete Module Registry

## Status

Approved for WP-212-I3 implementation.

## Purpose

Define the first concrete registry for declared SIF modules. This increment governs registration, deterministic inspection, duplicate rejection, and irreversible freeze semantics. Dependency resolution remains outside this increment.

## Requirements

1. The registry MUST accept instances of `ModuleInterface` only.
2. Registration MUST index modules by the exact value of `ModuleId`.
3. A second module with an already registered identifier MUST be rejected, regardless of descriptor equality or version.
4. Descriptor enumeration MUST preserve registration order.
5. Missing lookups MUST return `null`; presence checks MUST return `false`.
6. Freezing MUST be irreversible and idempotent.
7. A frozen registry MUST reject every subsequent registration attempt without changing existing state.
8. Registry mutation failures MUST use typed exceptions and MUST NOT expose descriptor metadata.
9. Read operations MUST remain available after freezing.
10. This increment MUST NOT resolve dependencies, constraints, conflicts, capabilities, or provider order.

## Public API

- `ModuleRegistryInterface::has()`
- `ModuleRegistryInterface::descriptor()`
- `ModuleRegistryInterface::descriptors()`
- `ModuleRegistryInterface::isFrozen()`
- `MutableModuleRegistryInterface::register()`
- `MutableModuleRegistryInterface::freeze()`
- `ModuleRegistry`

## Exceptions

- `ModuleRegistryException`
- `DuplicateModuleException`
- `FrozenModuleRegistryException`

## Determinism

The registry uses insertion order as its only ordering rule. Topological or lexical ordering belongs to the future resolver and MUST NOT be inferred by this component.

## Compatibility

This increment extends the two registry contracts introduced in I2. No concrete implementation existed before I3, therefore no runtime behavior is displaced. Modules remain declarative descriptor providers.

## Deferred work

- version constraint evaluation;
- dependency graph construction;
- cycle and conflict detection;
- enablement policy;
- resolution plans and fingerprints;
- runtime/bootstrap integration.
