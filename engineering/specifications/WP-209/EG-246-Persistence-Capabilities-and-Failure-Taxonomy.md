---
id: EG-246
title: Persistence Capabilities and Failure Taxonomy
summary: Defines explicit adapter capabilities, immutable capability sets, capability guards, stable persistence failure categories, typed exceptions, and original-cause preservation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-209
tags:
  - foundation
  - persistence
  - capabilities
  - failures
  - exceptions
depends_on:
  - EG-245
  - EG-244
  - EG-243
  - EG-242
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-246 — Persistence Capabilities and Failure Taxonomy

## Purpose

This specification defines explicit adapter capabilities and a stable public failure taxonomy for the Persistence subsystem.

The increment does not implement a database adapter, SQL execution, retries, logging, diagnostics transport, or automatic exception translation.

## PersistenceCapability

The initial capability vocabulary includes:

- transactions;
- nested transactions;
- savepoints;
- read-only transactions;
- query criteria;
- sorting;
- offset pagination;
- projections;
- streaming results;
- optimistic concurrency;
- Unit of Work.

A capability indicates declared support. It does not prove runtime availability or health.

## PersistenceCapabilities

`PersistenceCapabilities` is an immutable, deterministic set.

It:

- removes duplicates;
- sorts capabilities by stable value;
- supports immutable addition and removal;
- exposes explicit support checks;
- contains no adapter configuration.

## Capability providers

`PersistenceCapabilityProviderInterface` exposes an adapter or component capability set.

Repositories, transaction managers, connections, or complete adapters may implement it when capability discovery is meaningful.

The Core does not require every persistence object to implement this contract.

## Capability guard

`PersistenceCapabilityGuard` enforces a required capability explicitly.

Unsupported capabilities fail with `UnsupportedPersistenceCapabilityException`.

The guard does not negotiate fallbacks or silently degrade behavior.

## Failure taxonomy

`PersistenceFailureKind` defines stable categories:

- connection;
- transaction;
- query;
- mapping;
- concurrency;
- repository;
- Unit of Work;
- unsupported capability;
- unknown.

These categories remain independent of driver error codes.

## PersistenceFailureInterface

A public persistence failure exposes:

- stable failure kind;
- optional operation name;
- optional original cause.

The operation name is semantic, such as:

- `connect`;
- `commit`;
- `repository.query`;
- `mapper.hydrate`.

It is not required to expose SQL text or credentials.

## PersistenceException

`PersistenceException` is the stable base exception.

It preserves the original throwable through both:

- `cause`;
- native exception chaining.

Public messages should remain safe and technology-neutral.

Adapters must avoid placing credentials, connection strings, bound values, or confidential SQL content in public messages.

## Typed failures

The initial typed failures include:

- `ConnectionFailureException`;
- `TransactionFailureException`;
- `QueryFailureException`;
- `MappingFailureException`;
- `ConcurrencyConflictException`;
- `RepositoryFailureException`;
- `UnitOfWorkFailureException`;
- `UnsupportedPersistenceCapabilityException`.

Adapters may introduce more specific subclasses while preserving the stable public taxonomy.

## Translation boundary

Concrete adapters are responsible for translating native failures.

For example, a PDO or ODBC exception may become a `QueryFailureException` while remaining available as the cause.

Applications should catch stable Persistence exceptions rather than driver-specific exceptions.

## Exclusions

This increment does not implement:

- native exception translation;
- SQL-state mapping;
- retry policies;
- logging;
- telemetry;
- diagnostics redaction;
- adapter health checks;
- capability negotiation;
- automatic fallback;
- database adapters.

## Acceptance criteria

- capabilities are explicit and immutable;
- duplicate capabilities normalize deterministically;
- unsupported operations fail predictably;
- failure categories are stable and driver-neutral;
- original causes are preserved;
- public messages need not expose native details;
- no SQL or driver dependency is introduced;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
