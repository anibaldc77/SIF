---
id: WP-210-I4-REVIEW
title: WP-210-I4 Constructor Autowiring and Argument Bindings Implementation Review
summary: Reviews opt-in constructor autowiring, explicit values, service references, type resolution, default and nullable behavior, compatibility, and typed parameter failures.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-210
tags:
  - foundation
  - container
  - autowiring
  - bindings
  - review
depends_on:
  - EG-252
  - EG-251
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-210-I4 — Implementation Review

## Scope

WP-210-I4 implements:

- constructor binding kinds;
- explicit value bindings;
- explicit service-reference bindings;
- immutable parameter binding collections;
- opt-in autowired class definitions;
- named-type dependency resolution;
- scalar binding enforcement;
- default parameter support;
- nullable fallback;
- union and intersection rejection without explicit bindings;
- typed constructor parameter failures;
- deterministic tests;
- governed documentation.

## Compatibility

`ServiceDefinition::forClass` remains non-autowired.

Existing WP-210-I2 and WP-210-I3 behavior therefore remains compatible.

The new `forAutowiredClass` factory is additive.

## Resolution review

The implemented precedence is:

1. explicit binding;
2. registered named service type;
3. default value;
4. nullable fallback;
5. failure.

This matches the approved architecture within the current increment's scope.

Contextual bindings will later be inserted between explicit bindings and direct type resolution.

## Scalar review

Scalar values are never guessed.

They must be explicitly supplied or provided by PHP defaults or nullable semantics.

No direct environment or configuration dependency is introduced.

## Type review

Union and intersection parameters are intentionally rejected unless explicitly bound.

This avoids ambiguous service selection.

Native PHP constructor type checks remain the final validation boundary.

## Security review

Bindings contain values but diagnostics expose parameter names only.

The implementation does not serialize or print explicit binding values.

## Recommendation

Approve WP-210-I4 after the complete quality gate passes.

Continue with WP-210-I5, limited to:

- scope identifiers;
- explicit scope creation and closure;
- scoped service storage;
- scoped lifetime resolution;
- nested scope policy;
- missing-active-scope failures;
- deterministic scoped identity.

Tags, contextual bindings, lazy services, compilation, and compatibility remain excluded.
