---
id: EG-252
title: Constructor Autowiring and Explicit Argument Bindings
summary: Defines opt-in constructor autowiring, parameter-name bindings, service references, explicit scalar values, defaults, nullable dependencies, type-resolution precedence, and typed parameter failures.
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
  - autowiring
  - constructor
  - bindings
depends_on:
  - EG-251
  - EG-250
  - EG-249
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-252 — Constructor Autowiring and Explicit Argument Bindings

## Purpose

This specification adds opt-in constructor autowiring and explicit argument bindings to Container 2.0.

Autowiring remains disabled for existing `forClass` definitions.

## Opt-in model

Autowiring is enabled through:

```php
ServiceDefinition::forAutowiredClass(...)
```

This preserves WP-210-I3 behavior for existing class definitions.

## Constructor bindings

Bindings are indexed by constructor parameter name.

A binding may contain:

- an explicit value;
- a service identifier reference.

Bindings are immutable and deterministically ordered.

## Resolution precedence

For each constructor parameter, resolution occurs in this order:

1. explicit parameter binding;
2. registered non-builtin type identifier;
3. default value;
4. nullable fallback;
5. typed failure.

## Service type resolution

A non-builtin named type is converted to an exact `ServiceIdentifier` using its declared type name.

The dependency must already have a registered definition.

This increment does not implicitly register arbitrary classes.

## Scalar values

Builtin parameters require explicit bindings unless they have:

- a default value;
- nullable semantics.

The container does not read configuration or environment variables directly.

## Union and intersection types

Union and intersection types require explicit bindings.

The container does not guess among candidate types.

## Default values

Default constructor values are preserved when no explicit binding or registered service applies.

## Nullable dependencies

A nullable dependency resolves to `null` only after explicit bindings, registered service lookup, and defaults have been considered.

## Type responsibility

Explicit values are passed to reflection construction.

PHP's native constructor type enforcement remains authoritative.

Type errors are translated through `ServiceCreationException` with the original cause preserved.

## Failure taxonomy

The increment adds:

- `InvalidConstructorBindingException`;
- `UnresolvableConstructorParameterException`.

The parameter failure exposes the unresolved parameter name.

## Exclusions

This increment does not implement:

- contextual bindings;
- global scalar parameter providers;
- environment lookup;
- configuration lookup;
- implicit registration of arbitrary classes;
- union-type selection;
- intersection-type composition;
- variadic injection;
- attributes;
- property injection;
- method injection;
- scopes;
- tags;
- lazy services;
- compilation;
- reflection caching.

## Acceptance criteria

- autowiring is opt-in;
- previous non-autowired class behavior remains intact;
- explicit bindings take precedence;
- service references may use arbitrary identifiers;
- registered class and interface identifiers resolve;
- scalars require explicit values unless defaulted or nullable;
- defaults are preserved;
- nullable dependencies may resolve to null;
- union and intersection types fail without explicit bindings;
- parameter failures expose parameter names;
- PHP native type checks remain authoritative;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
