---
id: WP-210-I1-REVIEW
title: WP-210-I1 Dependency Injection Container Architecture Review
summary: Reviews the proposed architecture for service definitions, lifetimes, scopes, aliases, autowiring, contextual bindings, tags, lazy services, diagnostics, compilation, and compatibility.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-210
tags:
  - foundation
  - container
  - dependency-injection
  - architecture
  - review
depends_on:
  - EG-249
  - EG-248
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-210-I1 — Dependency Injection Container Architecture Review

## Scope

WP-210-I1 defines the architecture of Dependency Injection Container 2.0.

It adds no production PHP code and makes no change to the current container.

## Reviewed decisions

The architecture establishes:

- explicit immutable definitions;
- opaque service identifiers;
- transient, singleton, and scoped lifetimes;
- deterministic alias resolution;
- explicit scope creation;
- constructor-first autowiring;
- explicit scalar bindings;
- contextual bindings;
- deterministic tagged services;
- opt-in lazy resolution;
- mandatory cycle detection;
- safe resolution diagnostics;
- future compilation support;
- compatibility planning for the current container.

## Dependency review

The approved direction is:

```text
Application / Module
        -> Container contracts
        -> Container implementation
        -> Definitions and factories
```

Arbitrary services should not depend on the concrete container.

Constructor injection remains preferred.

## Autowiring review

The proposed precedence is valid:

1. argument override;
2. contextual binding;
3. direct definition;
4. alias;
5. autowiring candidate;
6. default value;
7. failure.

This order reduces ambiguity and preserves explicit application control.

## Scope review

Scopes are defined generically and are not coupled to HTTP requests.

This is appropriate for SIF because the same runtime may host:

- web requests;
- CLI commands;
- background jobs;
- tests;
- transactions;
- tenant-specific work.

## Alias review

Aliases must preserve singleton and scoped identity.

Alias resolution must be normalized before instance lookup to avoid duplicated instances.

## Lazy service review

Lazy resolution is correctly treated as opt-in.

The architecture does not commit yet to a proxy implementation, allowing later evaluation of:

- runtime-generated proxies;
- generated code;
- closures;
- lazy references.

## Tag review

Deterministic tag ordering is required.

Priority and insertion order should be formally specified before implementation.

## Compatibility review

The current container must be inventoried before migration.

A compatibility adapter is preferable to a disruptive replacement.

The public `Framework.php` access point should remain stable.

## Risk review

Primary risks include:

1. turning the container into a service locator;
2. ambiguous autowiring;
3. hidden global state;
4. inconsistent scoped lifetimes;
5. alias-created duplicate instances;
6. reflection overreach;
7. proxy complexity;
8. breaking current container behavior;
9. leaking secrets through diagnostics;
10. non-deterministic tag ordering.

The architecture mitigates these risks through explicit definitions, constructor injection, immutable resolution context, deterministic ordering, safe diagnostics, and staged compatibility.

## Recommendation

Approve WP-210-I1.

Continue with WP-210-I2, limited to:

- service identifiers;
- lifetimes;
- immutable definitions;
- aliases;
- definition validation;
- core container contracts;
- deterministic unit tests.

WP-210-I2 should not yet implement autowiring, scopes, contextual bindings, tags, lazy services, compilation, or migration of the existing container.
