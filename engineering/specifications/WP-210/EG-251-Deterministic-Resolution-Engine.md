---
id: EG-251
title: Deterministic Resolution Engine and Cycle Detection
summary: Defines the interpreted resolution engine for instance, factory, zero-argument class, alias, transient, and singleton definitions, including resolution paths, cycle detection, identity semantics, and typed creation failures.
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
  - resolution
  - cycles
  - singleton
depends_on:
  - EG-250
  - EG-249
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-251 — Deterministic Resolution Engine and Cycle Detection

## Purpose

This specification defines the first interpreted resolution engine for Container 2.0.

It resolves explicitly registered definitions without autowiring.

## Supported definitions

The engine resolves:

- existing instances;
- factories;
- instantiable classes with no required constructor arguments;
- aliases and alias chains.

## Supported lifetimes

The engine implements:

- transient;
- singleton.

Scoped definitions fail with a typed exception until WP-210-I5.

## Instance semantics

Existing instance definitions always return the exact registered object.

## Transient semantics

Transient class and factory definitions produce a new object for every independent resolution.

## Singleton semantics

Singleton definitions are created once per container and cached by terminal definition identifier.

Aliases do not own a separate singleton cache entry.

Resolving an alias and resolving its terminal target return the same singleton object.

## Factory semantics

Factories receive `ServiceContainerInterface`.

A factory may explicitly resolve other services.

Factory failures are translated into `ServiceCreationException` while preserving the original throwable.

## Class semantics

Class definitions are instantiated through reflection.

This increment permits only:

- no constructor;
- constructor with no required arguments.

Classes requiring dependencies fail with `UnresolvableServiceException`.

Constructor autowiring belongs to WP-210-I4.

## Alias resolution

Alias chains are resolved recursively.

The engine:

- preserves the original request path;
- follows exact target identifiers;
- detects alias cycles;
- does not create alias-owned instances.

A missing alias target fails through the registry's typed missing-definition exception.

## ResolutionPath

`ResolutionPath` is an immutable ordered list of service identifiers.

It supports:

- append;
- containment checks;
- count;
- deterministic formatting.

Example:

```text
mailer -> transport -> configuration
```

## Cycle detection

The engine detects:

- direct self-resolution through factories;
- indirect factory cycles;
- alias cycles;
- mixed alias and factory cycles.

Cycle detection occurs before recursion exhaustion.

The failure exposes the complete path including the repeated terminal identifier.

## Failure taxonomy

The increment introduces:

- `ContainerResolutionException`;
- `CircularServiceDependencyException`;
- `ServiceCreationException`;
- `UnresolvableServiceException`;
- `UnsupportedServiceLifetimeException`.

Resolution failures expose:

- requested identifier;
- resolution path;
- optional original cause.

## Cache management

The resolver exposes:

- `forget`;
- `clearSingletons`.

For aliases, `forget` removes the terminal target's singleton.

These operations do not modify definitions.

## has semantics

`has` reports whether the exact requested identifier has a registered definition.

It does not validate the complete alias chain or guarantee successful creation.

## Exclusions

This increment does not implement:

- constructor autowiring;
- scalar bindings;
- default-parameter resolution;
- union types;
- intersection types;
- scopes;
- scoped storage;
- contextual bindings;
- tags;
- lazy services;
- proxies;
- compilation;
- reflection caching;
- disposal;
- legacy-container compatibility.

## Acceptance criteria

- instances preserve identity;
- transient definitions create new objects;
- singleton definitions preserve container-local identity;
- aliases preserve target identity;
- alias chains resolve deterministically;
- class creation is limited to zero-required-argument constructors;
- factories may resolve explicit dependencies;
- factory causes are preserved;
- all cycles fail before recursion exhaustion;
- resolution paths are stable;
- scoped lifetime fails predictably;
- active paths are cleared after resolution;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
