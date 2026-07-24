---
id: EG-210
title: Environment Providers
summary: Deterministic provider abstractions for native, array-backed and composed process environment values.
status: Draft for Review
version: 0.1.1
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - environment
  - foundation
  - providers
  - runtime
work_package: WP-204
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
increment: I1
---

# EG-210 — Environment Providers

## 1. Purpose

Define a small, deterministic and testable abstraction for reading process environment variables without coupling consumers to PHP superglobals or global functions.

## 2. Scope

This increment provides:

- a common environment-provider contract;
- an in-memory array provider;
- a native provider backed by `$_ENV`, `$_SERVER` and `getenv()`;
- configurable source precedence;
- deterministic composition of multiple providers;
- explicit validation failures;
- unit tests.

This increment does not parse `.env` files and does not integrate environment providers into `Bootstrap` or `Application`.

## 3. Contract

`EnvironmentProviderInterface` exposes:

- `has(string $key): bool`;
- `get(string $key, ?string $default = null): ?string`;
- `all(): array<string, string>`.

Keys are trimmed and must not be empty. Values are normalized to strings. Null values are omitted because they represent absence.

## 4. Providers

### 4.1 ArrayEnvironmentProvider

Provides immutable snapshot semantics over an injected array. It is the canonical provider for tests, adapters and explicitly supplied environment data.

### 4.2 NativeEnvironmentProvider

Captures a snapshot from three native sources:

1. `$_SERVER`;
2. `$_ENV`;
3. `getenv()`.

The default order is listed from lowest to highest precedence, therefore process values returned by `getenv()` win by default. Applications may provide a different complete ordering.

The provider must receive each source exactly once. Missing, duplicate or unknown source identifiers are invalid.

### 4.3 CompositeEnvironmentProvider

Composes zero or more providers. Providers are ordered from highest to lowest precedence: the first provider containing a key wins. `all()` must expose the same effective values as repeated `get()` calls.

## 5. Invariants

- provider snapshots do not mutate after construction;
- lookup and bulk enumeration share identical precedence semantics;
- empty keys fail explicitly;
- arrays and objects are not accepted as environment values;
- provider composition remains deterministic;
- no component depends on configuration loading or runtime bootstrap.

## 6. Deferred work

- `.env` parsing belongs to WP-204-I2;
- runtime/application integration belongs to WP-204-I3;
- typed environment conversion may be introduced by a later configuration-facing adapter.

## 7. Acceptance criteria

- PHP 8.2 syntax validation succeeds;
- all provider unit tests pass;
- PHPStan level 8 reports no errors;
- the complete repository test suite remains green;
- SIF Builder reports zero diagnostics and deterministic artifacts.
