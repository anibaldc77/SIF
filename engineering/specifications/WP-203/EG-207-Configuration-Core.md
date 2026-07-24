---
id: EG-207
title: Configuration Core
summary: Defines the Foundation configuration repository, dot-notation access model, mutation boundaries, and freeze semantics for application-scoped configuration.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - foundation
  - configuration
  - repository
  - immutability
  - runtime
work_package: WP-203
depends_on:
  - EG-206
  - ADR-0005
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-207 — Configuration Core

## 1. Purpose

WP-203-I1 establishes the framework-neutral configuration core used by later loaders and Runtime integration increments.

The increment defines a deterministic in-memory repository with dot-notation reads, explicit required-value resolution, controlled mutation, and irreversible freezing.

## 2. Contracts

`ConfigurationInterface` defines read access:

- `has(string $key): bool`;
- `get(string $key, mixed $default = null): mixed`;
- `require(string $key): mixed`;
- `all(): array`;
- `isFrozen(): bool`.

`MutableConfigurationInterface` extends the read contract with:

- `set(string $key, mixed $value): void`;
- `replace(array $values): void`;
- `freeze(): void`.

Consumers SHOULD depend on `ConfigurationInterface` unless mutation is an explicit responsibility.

## 3. Key model

1. Configuration keys SHALL use dot notation for nested traversal.
2. Surrounding whitespace SHALL be removed at the repository boundary.
3. Empty keys SHALL be rejected.
4. Empty path segments SHALL be rejected.
5. Key comparison SHALL remain case-sensitive.
6. A defined `null` value SHALL be distinguishable from an absent key.

## 4. Read semantics

`has()` SHALL use key existence semantics rather than non-null semantics.

`get()` SHALL return the configured value when present and the caller-provided default when absent.

`require()` SHALL return a present value, including `null`, and SHALL throw `ConfigurationNotFoundException` when the key is absent.

`all()` SHALL return the complete configuration tree.

## 5. Mutation semantics

`set()` SHALL create missing intermediate arrays.

`set()` SHALL NOT silently replace a scalar intermediate node with an array. Such structural conflicts SHALL produce `InvalidConfigurationStructureException`.

`replace()` SHALL replace the complete configuration tree.

Loaders, merge policies, and source precedence are outside this increment and SHALL be specified by WP-203-I2.

## 6. Freeze semantics

`freeze()` SHALL be idempotent and irreversible for the lifetime of the repository instance.

After freezing, `set()` and `replace()` SHALL throw `FrozenConfigurationException`.

Read operations SHALL remain available after freezing.

The Runtime integration increment SHALL determine the lifecycle point at which application configuration becomes frozen.

## 7. Compatibility

This increment SHALL NOT modify:

- `ApplicationInterface`;
- `Application`;
- `Kernel`;
- `Lifecycle`;
- `Runtime`;
- capability contracts;
- service-provider contracts.

## 8. Acceptance criteria

- Top-level and nested values are readable.
- Missing values support defaults.
- Required missing values fail explicitly.
- Defined `null` values remain observable.
- Nested writes are deterministic.
- Structural collisions fail explicitly.
- Complete replacement is supported before freezing.
- Freeze is irreversible and idempotent.
- Invalid keys fail explicitly.
- PHPStan level 8 reports no errors.
