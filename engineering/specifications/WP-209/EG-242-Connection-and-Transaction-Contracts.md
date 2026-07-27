---
id: EG-242
title: Connection and Transaction Contracts
summary: Defines storage-neutral connection identities, explicit connection resolution, transaction execution contracts, typed states, lifecycle boundaries, and deterministic test doubles.
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
  - connection
  - transaction
  - contracts
depends_on:
  - EG-241
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-242 — Connection and Transaction Contracts

## Purpose

This specification defines the first production increment of the Persistence subsystem: storage-neutral connection and transaction boundaries.

The increment does not implement PDO, ODBC, SQL, database drivers, repositories, queries, mappers, Unit of Work, or database-specific adapters.

## ConnectionName

`ConnectionName` is an immutable opaque identifier for a configured connection.

It:

- preserves any non-empty string;
- provides a conventional `default` value;
- supports explicit equality;
- does not encode driver, host, database, tenant, or credentials.

## ConnectionInterface

A connection exposes only:

- its `ConnectionName`;
- whether it is open;
- explicit closure.

The contract does not expose:

- raw PDO or ODBC handles;
- SQL execution;
- transactions;
- driver metadata;
- DSNs;
- credentials.

Technology-specific adapters may provide additional interfaces outside the Core.

## ConnectionManagerInterface

A connection manager resolves:

- the configured default connection; or
- an explicitly named connection.

Resolution is deterministic and explicit.

The manager does not inspect global state or Execution Context automatically.

## ConnectionRegistry

`ConnectionRegistry` is a storage-neutral explicit registry.

It:

- registers connection instances by name;
- rejects duplicate names;
- resolves named connections;
- resolves a configured default;
- permits explicit default replacement only with a registered connection.

It does not create or reopen connections.

## TransactionManagerInterface

The transaction manager executes a callback within an explicit transaction boundary.

The contract guarantees:

- callback return preservation;
- explicit state inspection;
- explicit transaction depth;
- exception propagation.

Actual begin, commit, rollback, savepoint, and driver behavior remain adapter responsibilities.

## TransactionState

The initial state model contains:

- `idle`;
- `active`;
- `committed`;
- `rolled_back`.

This value describes the latest known transaction lifecycle state. It does not imply database durability.

## Nested transactions

Nested transaction support is adapter-specific.

The deterministic reference double rejects nesting with `NestedTransactionNotSupportedException`.

Future adapters may support savepoints or nested semantics through explicit capabilities.

## Test doubles

The increment includes deterministic test fixtures:

- `InMemoryConnection`;
- `InMemoryTransactionManager`.

They validate contract semantics only and are not production persistence adapters.

## Exclusions

This increment does not implement:

- SQL execution;
- statements;
- query objects;
- repositories;
- mapping;
- Unit of Work;
- retry;
- timeouts;
- savepoints;
- distributed transactions;
- database configuration;
- connection pooling;
- health checks;
- Audit integration.

## Acceptance criteria

- connection identity is immutable;
- connection resolution is explicit;
- duplicate and missing connections fail predictably;
- connection lifecycle remains adapter-owned;
- transactions preserve callback results;
- failures trigger rollback semantics in the reference double;
- original failures propagate;
- nested transaction policy is explicit;
- PHPStan level 8 passes;
- PHPUnit passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
