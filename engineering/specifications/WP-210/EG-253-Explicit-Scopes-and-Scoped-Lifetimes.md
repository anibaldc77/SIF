---
id: EG-253
title: Explicit Scopes and Scoped Lifetimes
summary: Defines explicit service scopes, nested scope behavior, scoped instance storage, lifecycle closure, current-scope propagation through factories and autowiring, and deterministic scoped identity.
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
  - scopes
  - lifetime
  - dependency-injection
depends_on:
  - EG-252
  - EG-251
  - EG-250
  - EG-249
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-253 — Explicit Scopes and Scoped Lifetimes

## Purpose

This specification implements explicit service scopes and the `scoped` lifetime in Container 2.0.

Scopes are generic and are not coupled to HTTP.

## ScopeIdentifier

A scope identifier is a non-empty opaque string.

Examples include:

- `request-42`;
- `command-import`;
- `job-2026-07-27`;
- `tenant-acme`;
- `test-case-17`.

Identifiers are descriptive and do not need to be globally unique.

## Explicit creation

Scopes are created explicitly through:

```php
$container->beginScope(new ScopeIdentifier('request-42'));
```

No ambient global scope is introduced.

## Scoped identity

A scoped definition:

- creates one instance per active scope;
- preserves identity within that scope;
- creates a different instance in another scope;
- remains isolated from singleton storage.

## Root resolution

Resolving a scoped definition from the root container fails with `MissingActiveServiceScopeException`.

The container never silently degrades a scoped service to transient or singleton behavior.

## Nested scopes

A scope may create a child scope.

Child scopes:

- preserve a reference to the parent scope;
- maintain independent scoped instances;
- share container singletons;
- do not reuse parent scoped instances;
- become unusable if an ancestor closes.

## Scope closure

Closing a scope:

- clears its scoped instances;
- is idempotent;
- rejects subsequent resolution;
- rejects child resolution through ancestor validation;
- does not clear root singletons;
- does not modify definitions.

Automatic disposal of service objects remains outside this increment.

## Factory propagation

Factories resolving inside a scope receive the scoped container, not the root container.

Nested factory lookups therefore preserve the active scope.

## Autowiring propagation

Autowired dependencies resolve through the current scope.

A scoped consumer and its scoped dependency therefore share the same scope-local instance.

## Alias behavior

Aliases continue to normalize to terminal definitions.

A scoped alias and its scoped target share the same scope-local instance.

## Cache management

Calling `forget` on a scope:

- removes a scoped terminal instance from that scope only;
- delegates singleton removal to the root singleton cache;
- does not affect sibling scopes.

## Singleton and transient behavior

Singleton definitions remain shared across all scopes belonging to the same root container.

Transient definitions continue to produce new instances for each resolution.

## Failure taxonomy

The increment adds:

- `InvalidScopeIdentifierException`;
- `ClosedServiceScopeException`;
- `MissingActiveServiceScopeException`.

## Exclusions

This increment does not implement:

- automatic service disposal;
- request integration;
- command integration;
- job integration;
- asynchronous scope propagation;
- thread-local or fiber-local scopes;
- contextual bindings;
- tags;
- lazy services;
- proxy generation;
- compilation;
- compatibility adapter.

## Acceptance criteria

- scope identifiers are explicit;
- root resolution of scoped services fails;
- scoped identity is stable within one scope;
- sibling scopes are isolated;
- child scopes are isolated from parents;
- factories receive the scoped resolver;
- autowiring preserves the active scope;
- singletons are shared across scopes;
- closing scopes clears local state;
- closed scopes reject further use;
- closing a parent invalidates children;
- forgetting a scoped service affects only the current scope;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
