---
id: WP-210-I5-REVIEW
title: WP-210-I5 Explicit Scopes and Scoped Lifetimes Implementation Review
summary: Reviews explicit scope creation, nested scopes, scoped instance storage, closure semantics, scope propagation through factories and autowiring, cache isolation, and typed failures.
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
  - scopes
  - lifetime
  - review
depends_on:
  - EG-253
  - EG-252
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-210-I5 — Implementation Review

## Scope

WP-210-I5 implements:

- `ScopeIdentifier`;
- `ScopedServiceContainerInterface`;
- `ServiceScopeInterface`;
- `ServiceScopeState`;
- `ServiceScope`;
- scoped lifetime resolution;
- nested scope creation;
- scope-local caches;
- scope closure;
- current-scope propagation through factories;
- current-scope propagation through autowiring;
- scoped `forget`;
- typed scope failures;
- deterministic unit tests;
- governed documentation.

## Architectural compliance

The implementation introduces no global active scope.

Every scope is an explicit object.

The root container remains responsible for:

- definitions;
- singleton storage;
- shared resolution paths.

Each scope remains responsible for:

- its identifier;
- parent state;
- scoped instances;
- closure state.

## Identity review

Scoped instances are cached by terminal definition identifier within the current `ServiceScopeState`.

Singleton identity remains root-container local.

Transient behavior remains uncached.

## Nested scope review

Nested scopes are isolated rather than inheriting parent scoped instances.

This behavior is safer for generic request, command, job, tenant, and test boundaries.

A child validates every ancestor before use.

## Propagation review

Factories receive `ServiceScope` when invoked inside a scope.

Autowired dependencies are resolved using the same current scope state.

This prevents accidental root resolution and lifetime degradation.

## Closure review

Closing a scope clears its instance map and marks it closed.

Object disposal is deliberately deferred because no disposal contract has yet been approved.

## Compatibility

The root `get()` behavior for transient and singleton services remains unchanged.

The previous `UnsupportedServiceLifetimeException` path is replaced by functional scoped resolution and `MissingActiveServiceScopeException` when no scope is active.

## Recommendation

Approve WP-210-I5 after the complete quality gate passes.

Continue with WP-210-I6, limited to:

- contextual constructor bindings;
- service tags;
- deterministic tag ordering;
- tag metadata and priorities;
- tagged service discovery;
- contextual precedence integration.

Lazy services, compilation, disposal, and legacy compatibility remain excluded.
