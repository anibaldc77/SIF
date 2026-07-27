---
id: EG-247
title: In-Memory Reference Adapter
summary: Defines a deterministic production reference adapter integrating connection lifecycle, transactions, capabilities, query evaluation, mapping, storage, and repositories entirely in memory.
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
  - memory
  - adapter
  - reference
depends_on:
  - EG-246
  - EG-245
  - EG-244
  - EG-243
  - EG-242
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-247 — In-Memory Reference Adapter

## Purpose

This specification defines the first complete Persistence adapter.

The adapter is deterministic and stores all data in process memory.

It exists to validate Core contracts and provide a usable reference for tests, examples, prototypes, and future database adapter design.

It is not a durability mechanism.

## Components

The reference adapter contains:

- `InMemoryConnection`;
- `InMemoryTransactionManager`;
- `InMemoryStorage`;
- `InMemoryQueryEvaluator`;
- generic `InMemoryRepository<T>`.

## InMemoryConnection

The connection:

- has explicit lifecycle;
- may be closed and reopened;
- declares supported capabilities;
- performs no network or filesystem I/O;
- contains no credentials.

## InMemoryTransactionManager

The manager:

- executes callbacks explicitly;
- preserves callback return values;
- exposes typed state and depth;
- rolls back semantically on failure;
- rejects nested transactions;
- declares transaction capability.

The transaction does not provide physical rollback of arbitrary external mutations. It validates contract behavior only.

## InMemoryStorage

The storage contains named collections of `StorageRecord` objects.

It supports:

- put;
- get;
- remove;
- list;
- count;
- clear.

Identifiers are normalized to strings internally.

Storage is process-local and non-durable.

## InMemoryQueryEvaluator

The evaluator applies query intent in this order:

1. criteria;
2. sorting;
3. offset pagination;
4. projection.

It supports all initial `QueryOperator` values.

Criteria are combined with logical conjunction.

Sorting preserves declared field priority.

Projection creates new `StorageRecord` objects containing only requested fields.

## InMemoryRepository

The generic repository composes:

- repository metadata;
- managed class;
- collection name;
- explicit mapper;
- storage;
- query evaluator;
- identifier resolver callback.

It supports:

- save;
- remove;
- find by identifier;
- query;
- capability discovery.

The repository translates internal failures into `RepositoryFailureException` while preserving causes.

## Type safety

The repository validates that saved and removed objects belong to its declared managed type.

Mapper generic types remain checked through PHPStan annotations.

## Declared capabilities

The reference adapter declares support for:

- transactions;
- query criteria;
- sorting;
- offset pagination;
- projection;
- Unit of Work composition.

It does not declare support for:

- nested transactions;
- savepoints;
- streaming;
- optimistic concurrency;
- durable rollback;
- distributed transactions.

## Exclusions

This increment does not implement:

- SQL;
- PDO;
- ODBC;
- files;
- durable persistence;
- crash recovery;
- locking;
- concurrent process access;
- optimistic versioning;
- relation loading;
- schema management;
- migrations;
- automatic Unit of Work flush.

## Acceptance criteria

- the adapter composes all previously approved contracts;
- connection lifecycle is explicit;
- transactions are deterministic;
- query criteria are evaluated;
- sorting precedes pagination;
- projection is applied explicitly;
- mapping remains contract-based;
- repository type violations fail predictably;
- capabilities are declared;
- no external I/O occurs;
- PHPUnit passes;
- PHPStan level 8 passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
