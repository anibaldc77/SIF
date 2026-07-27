---
id: EG-230
title: Context Propagation and Scoped Execution
summary: Defines explicit context carriers and immutable scopes for propagating execution context without ambient or global state.
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
  - propagation
  - scope
  - execution
depends_on:
  - EG-229
  - EG-228
  - EG-227
  - EG-226
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-230 — Context Propagation and Scoped Execution

## 1. Purpose

This specification defines explicit execution-context propagation and scoped execution without ambient, process-global, thread-local, fiber-local, or container-global state.

## 2. Public contracts and types

The increment SHALL provide:

- `ContextCarrierInterface`;
- `ExecutionContextScopeInterface`;
- `ContextCarrier`;
- `ExecutionContextScope`.

## 3. Explicit propagation

A context SHALL cross an application boundary only through an explicit method parameter, carrier, scope, event, or value object. The increment SHALL NOT provide `current()`, `setCurrent()`, static stacks, service locators, or implicit restoration behavior.

`ContextCarrier` SHALL preserve the exact `ExecutionContextInterface` instance supplied to it. Replacing a carried context SHALL create a new carrier unless the same instance is supplied.

## 4. Scoped execution

`ExecutionContextScope::run()` SHALL invoke the supplied callable with the exact context instance carried by the scope and SHALL return the callable result unchanged.

Exceptions raised by the callable SHALL propagate unchanged. The scope SHALL NOT catch, translate, report, or persist them.

## 5. Child derivation

A scope SHALL derive child scopes exclusively through `ExecutionContextFactoryInterface`. The derived scope SHALL follow the lineage, correlation, causation, metadata inheritance, and attribute merge rules established by EG-228.

Derivation SHALL NOT mutate the parent scope, parent context, or parent attributes.

## 6. Isolation

Nested or sequential scopes SHALL remain independent. Running a child scope SHALL NOT alter the context observed by a parent scope before or after the child operation.

This guarantee is achieved structurally through explicit references, not through hidden push/pop state.

## 7. Exclusions

This increment SHALL NOT include:

- ambient context access;
- static or singleton context storage;
- async-local, thread-local, or fiber-local facilities;
- Runtime, Event Dispatcher, Observation, or Audit integration;
- HTTP headers, CLI variables, queues, persistence, or transport serialization;
- automatic context creation or lifecycle management.

## 8. Acceptance criteria

The increment is accepted when exact context identity, callable result preservation, exception identity preservation, parent/child lineage, immutable attribute derivation, carrier replacement semantics, explicit carrier-to-scope composition, nested-scope isolation, PHPStan level 8, Builder validation, and the complete repository quality gate pass.
