---
id: EG-245
title: Repository and Unit of Work Contracts
summary: Defines minimal repository metadata, explicit read and write operations, immutable change sets, optional unit-of-work coordination, transaction composition, and deterministic in-memory fixtures.
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
  - repository
  - unit-of-work
  - contracts
depends_on:
  - EG-244
  - EG-243
  - EG-242
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-245 — Repository and Unit of Work Contracts

## Purpose

This specification defines minimal repository and optional Unit of Work boundaries.

The increment does not implement SQL, database adapters, ORM behavior, BaseModel integration, identity maps, relations, lazy loading, or automatic dirty tracking.

## Repository ownership

Application-specific repositories remain preferred.

The Core provides only minimal generic contracts for shared infrastructure:

- repository name;
- managed application type;
- explicit read operations;
- explicit write operations.

Applications may define narrower interfaces and are not required to expose every generic operation.

## RepositoryInterface

`RepositoryInterface` exposes:

- immutable `RepositoryName`;
- managed application type.

It does not expose a database connection, table, schema, SQL, mapper, or driver.

## ReadRepositoryInterface

The initial generic read contract provides:

- `findById`;
- `query`.

It is optional and intended for adapters or application repositories that genuinely support these semantics.

## WriteRepositoryInterface

The initial generic write contract provides:

- `save`;
- `remove`.

It does not define insert versus update behavior. Adapters and application repositories own those semantics.

## RepositoryName

`RepositoryName` is an immutable non-empty opaque identifier.

It is not a table name and does not encode storage technology.

## ChangeSet

`ChangeSet` is an immutable description of tracked objects:

- new;
- dirty;
- removed.

It preserves object identity and order.

It does not contain field-level diffs.

## UnitOfWorkInterface

The Unit of Work contract supports:

- explicit registration of new objects;
- explicit registration of dirty objects;
- explicit registration of removed objects;
- commit;
- clear;
- state inspection;
- emptiness checks.

Registration is explicit. There is no automatic object inspection or dirty tracking.

## InMemoryUnitOfWork

The reference implementation coordinates a `ChangeSet` through `TransactionManagerInterface`.

It:

- deduplicates by object identity;
- prevents an object from being simultaneously new and dirty;
- lets removal supersede new and dirty tracking;
- executes apply logic inside an explicit transaction;
- clears tracked objects only after successful commit;
- preserves changes after failure;
- exposes typed state.

The protected `apply` hook is for deterministic reference coordination only.

Production adapters should compose or override behavior explicitly.

## UnitOfWorkState

The state model includes:

- clean;
- pending;
- committing;
- committed;
- failed.

## Exclusions

This increment does not implement:

- SQL repositories;
- database writes;
- identity map;
- snapshots;
- field-level dirty tracking;
- cascading;
- relations;
- flush ordering;
- generated identifiers;
- optimistic locking;
- automatic transaction creation outside commit;
- BaseModel hooks.

## Acceptance criteria

- repository metadata is explicit;
- read and write operations are storage-neutral;
- repositories expose no raw connection or SQL;
- Unit of Work remains optional;
- tracking is explicit and identity-based;
- change sets are immutable;
- commit occurs inside a transaction;
- successful commits clear tracked objects;
- failed commits preserve tracked objects;
- no ORM dependency is introduced;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
