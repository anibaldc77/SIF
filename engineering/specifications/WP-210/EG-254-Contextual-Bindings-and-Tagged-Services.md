---
id: EG-254
title: Contextual Bindings and Tagged Services
summary: Defines consumer-specific constructor bindings, contextual resolution precedence, service tags, metadata, deterministic priority ordering, and tagged service discovery for root and scoped containers.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-210
tags:
  - foundation
  - container
  - contextual-bindings
  - tagged-services
depends_on:
  - EG-253
  - EG-252
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-254 — Contextual Bindings and Tagged Services

## Purpose

This specification adds contextual constructor bindings and tagged service discovery to Container 2.0.

## Contextual bindings

A contextual binding selects a constructor dependency for one consumer and one parameter.

Example:

```text
when ContextualConsumer needs dependency
use contextual.example
```

The binding key is:

```text
consumer identifier + parameter name
```

## Contextual precedence

Constructor resolution precedence is now:

1. explicit definition-level constructor binding;
2. contextual binding;
3. registered non-builtin type identifier;
4. default value;
5. nullable fallback;
6. typed failure.

Definition-level bindings remain the strongest local override.

## Contextual registry

`ContextualBindingRegistry`:

- preserves registration order;
- rejects duplicate consumer/parameter pairs;
- stores immutable bindings;
- performs exact consumer and parameter matching.

This increment does not implement inheritance-based or wildcard contextual rules.

## Service tags

A service definition may declare zero or more tags.

A tag contains:

- non-empty name;
- integer priority;
- scalar-or-null metadata.

Aliases cannot declare tags.

## Tag ordering

Tagged discovery is deterministic.

Ordering rules:

1. higher priority first;
2. original service-definition registration order for equal priorities.

## Tagged discovery

The definition registry exposes tagged descriptors.

The root container and service scopes expose:

- `tagged(string $tag)`;
- `resolveTagged(string $tag)`.

Root discovery resolves services through the root container.

Scoped discovery resolves services through the current scope, preserving scoped identity.

## Metadata

Tag metadata is descriptive and must not contain objects, arrays, resources, or closures.

The initial model supports only:

- string;
- int;
- float;
- bool;
- null.

## Exclusions

This increment does not implement:

- wildcard contextual bindings;
- class hierarchy matching;
- interface hierarchy matching;
- tagged constructor injection;
- lazy tagged iterators;
- service decoration;
- compiler passes;
- automatic event listener registration;
- automatic middleware registration;
- lazy services;
- compilation;
- disposal;
- legacy compatibility.

## Acceptance criteria

- contextual bindings are exact and deterministic;
- duplicate contextual bindings fail;
- explicit local bindings override contextual bindings;
- contextual bindings override direct type lookup;
- service tags preserve metadata;
- tag priorities sort descending;
- equal priorities preserve definition order;
- root tagged resolution preserves lifetimes;
- scoped tagged resolution preserves scope-local identity;
- unknown tags return empty lists;
- aliases cannot own tags;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
