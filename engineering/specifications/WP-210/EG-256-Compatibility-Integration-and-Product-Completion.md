---
id: EG-256
title: Container Compatibility, Controlled Integration and Product Completion
summary: Defines the string-identifier compatibility adapter, explicit Container 2.0 composition factory, non-breaking framework integration boundary, migration strategy, reference integration, and WP-210 completion criteria.
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
  - compatibility
  - integration
  - product-completion
depends_on:
  - EG-255
  - EG-254
  - EG-253
  - EG-252
  - EG-251
  - EG-250
  - EG-249
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-256 — Container Compatibility, Controlled Integration and Product Completion

## Purpose

This specification closes WP-210 by defining an additive compatibility and integration boundary for Container 2.0.

The current framework bootstrap and public `Framework::create()` behavior remain unchanged.

## Compatibility principle

Container 2.0 SHALL NOT silently replace the existing application container during the alpha cycle.

Replacement requires:

- explicit application integration contract;
- migration plan;
- deprecation mapping;
- complete regression validation;
- SemVer decision.

## String identifier adapter

`StringServiceContainerAdapter` supports legacy-style string calls:

- `has(string)`;
- `get(string)`;
- `lazy(string)`;
- `beginScope(string)`.

The adapter converts strings to validated value objects and delegates all semantics to Container 2.0.

It does not duplicate:

- definitions;
- singleton state;
- scoped state;
- resolution logic;
- diagnostics.

## Native access

The compatibility adapter exposes its wrapped native container for controlled infrastructure integration.

Application code SHOULD migrate toward value-object and contract-based APIs.

## Composition factory

`ContainerCompositionFactory` creates one coherent composition containing:

- definition registry;
- contextual binding registry;
- resolver;
- validator;
- compiler;
- compatibility adapter.

All components share the same registries.

Definitions registered after composition creation are visible to the resolver, validator, and compiler.

## Framework boundary

This increment intentionally does not alter:

- `Framework::create()`;
- `Bootstrap`;
- `Application`;
- `Kernel`;
- service-provider lifecycle;
- existing public container access.

Container 2.0 is available as an explicit composition product.

A later application-integration work package may inject this composition through approved contracts.

## Reference integration

The product SHALL include:

- a vertical integration test;
- an executable reference example;
- validation;
- compilation;
- scoped resolution;
- contextual binding;
- tagged discovery;
- lazy reference;
- compatibility access.

## Migration path

Recommended migration stages:

1. compose Container 2.0 explicitly at infrastructure boundaries;
2. register new services in `ServiceDefinitionRegistry`;
3. use `StringServiceContainerAdapter` for string-based callers;
4. migrate consumers to constructor injection;
5. migrate infrastructure to `ServiceIdentifier`;
6. integrate with application bootstrap only after dedicated approval;
7. deprecate legacy access in a later release.

## Product scope completed

WP-210 delivers:

- architecture;
- core definitions and contracts;
- deterministic resolution;
- aliases and cycle detection;
- transient and singleton lifetimes;
- constructor autowiring;
- explicit argument bindings;
- scoped lifetime;
- nested scopes;
- contextual bindings;
- tags and metadata;
- lazy references;
- diagnostics;
- validation;
- deterministic compiled descriptions;
- compatibility adapter;
- explicit composition;
- vertical reference integration.

## Deferred scope

The following remain deferred:

- transparent proxy generation;
- executable compiled PHP;
- reflection metadata caching;
- automatic disposal;
- framework-wide container replacement;
- global static container;
- HTTP-specific scopes;
- fiber-local scopes;
- compiler passes;
- service decoration;
- automatic listener discovery.

## Completion criteria

WP-210 is complete when:

- all WP-210 specifications are indexed;
- all implementation reviews are indexed;
- complete PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics are zero;
- governed generation is deterministic;
- vertical reference test passes;
- reference example executes;
- compatibility remains additive;
- no current public bootstrap contract is broken.
