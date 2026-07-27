---
id: EG-250
title: Dependency Injection Core Definitions and Contracts
summary: Defines service identifiers, lifetimes, definition strategies, alias representation, registry behavior, validation rules, and the initial public contracts for Container 2.0.
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
  - definitions
  - contracts
  - aliases
depends_on:
  - EG-249
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-250 — Dependency Injection Core Definitions and Contracts

## Purpose

This specification defines the first production increment of Container 2.0.

It introduces immutable service descriptions and public contracts, but does not implement service resolution.

## ServiceIdentifier

`ServiceIdentifier` is a non-empty opaque string value.

It:

- does not require a class name;
- supports explicit equality;
- preserves the original value;
- performs no automatic normalization.

Identifiers remain case-sensitive.

## ServiceLifetime

The initial lifetime vocabulary contains:

- `transient`;
- `singleton`;
- `scoped`.

This increment models lifetimes only.

Scoped storage and active-scope enforcement belong to WP-210-I5.

## ServiceDefinitionKind

A definition declares exactly one strategy:

- concrete class;
- factory;
- existing instance;
- alias.

## ServiceDefinition

`ServiceDefinition` is immutable.

Class definitions contain:

- identifier;
- class-string;
- lifetime.

Factory definitions contain:

- identifier;
- closure accepting `ServiceContainerInterface`;
- lifetime.

Instance definitions contain:

- identifier;
- existing object;
- singleton lifetime.

Alias definitions contain:

- identifier;
- target identifier;
- no independent lifetime.

A self-targeting alias is invalid.

Alias chain and cycle resolution are deferred to WP-210-I3.

## ServiceContainerInterface

The initial public resolution contract exposes:

- `has`;
- `get`.

Both operations use `ServiceIdentifier`.

The contract returns objects only.

Scalar parameters belong to explicit bindings in WP-210-I4 and are not services in this contract.

## ServiceDefinitionRegistryInterface

The registry contract exposes:

- register;
- has;
- get;
- ordered listing.

A registry stores definitions but does not resolve them.

## ServiceDefinitionRegistry

The reference registry:

- preserves registration order;
- rejects duplicate identifiers;
- resolves exact identifiers;
- stores aliases as definitions;
- performs no alias traversal;
- creates no service instances.

## Validation

The increment validates:

- non-empty identifiers;
- non-empty class names;
- exactly one resolution strategy;
- lifetime presence for non-alias definitions;
- lifetime absence for aliases;
- no self-targeting alias;
- no duplicate identifier.

## Exclusions

This increment does not implement:

- service resolution;
- alias traversal;
- alias-cycle detection;
- constructor reflection;
- autowiring;
- scalar bindings;
- scopes;
- tags;
- contextual bindings;
- lazy services;
- proxy generation;
- compilation;
- compatibility adapter;
- Framework integration.

## Acceptance criteria

- identifiers are opaque and immutable;
- all lifetimes are modeled;
- definition strategies are mutually exclusive;
- aliases have no independent lifetime;
- instance definitions are singleton;
- factories are stored as typed closures;
- registration order is deterministic;
- duplicate definitions fail predictably;
- no resolution behavior is introduced;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
