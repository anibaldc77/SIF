---
id: EG-248
title: Persistence Reference Integration and Product Completion
summary: Defines the complete vertical reference flow, acceptance baseline, guarantees, exclusions, and formal product completion criteria for the storage-neutral SIF Persistence subsystem.
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
  - integration
  - completion
depends_on:
  - EG-247
  - EG-246
  - EG-245
  - EG-244
  - EG-243
  - EG-242
  - EG-241
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-248 — Persistence Reference Integration and Product Completion

## Purpose

This specification completes WP-209 by defining and validating a vertical reference flow across the storage-neutral Persistence subsystem.

No database-specific adapter, SQL dialect, migration engine, ORM, or BaseModel integration is introduced.

## Reference flow

The accepted vertical composition is:

```text
ConnectionName
        |
        v
InMemoryConnection
        |
        v
InMemoryTransactionManager
        |
        v
InMemoryStorage
        |
        v
InMemoryRepository<T>
        |
        v
Query + Criteria + Sorting + Pagination + Projection
        |
        v
InMemoryQueryEvaluator
        |
        v
MapperInterface<T>
        |
        v
ResultSet<T>
```

Every dependency is explicit and replaceable.

## Acceptance scenarios

The reference integration demonstrates:

1. explicit connection identity and lifecycle;
2. explicit transaction boundaries;
3. deterministic transaction states;
4. storage-neutral records;
5. explicit mapping;
6. repository save, find, query, and remove;
7. criteria evaluation;
8. deterministic sorting;
9. offset pagination;
10. projection;
11. capability declaration and guarding;
12. typed failure boundaries;
13. no external I/O;
14. no SQL or driver coupling.

## Product guarantees

WP-209 guarantees:

- storage neutrality;
- explicit connection resolution;
- explicit transaction boundaries;
- immutable query intent;
- explicit mapping;
- typed result sets;
- optional Unit of Work coordination;
- explicit capability discovery;
- stable failure taxonomy;
- original-cause preservation;
- application-owned repository interfaces;
- no reflection-based persistence;
- no hidden ambient transaction;
- no global connection state;
- PHP 8.2 compatibility;
- no external runtime dependency.

## Product exclusions

WP-209 does not include:

- SQL Server adapter;
- PostgreSQL adapter;
- MySQL adapter;
- SQLite adapter;
- PDO or ODBC wrappers;
- query generation;
- migrations;
- schema introspection;
- ORM;
- Active Record;
- BaseModel integration;
- relations;
- lazy loading;
- identity maps;
- automatic dirty tracking;
- optimistic version persistence;
- durable rollback;
- distributed transactions;
- retry policies;
- connection pooling;
- encryption;
- persistence logging.

Those concerns belong to later adapters or work packages.

## Completion baseline

WP-209 is complete when:

- increments I1 through I8 are integrated;
- the executable reference example runs successfully;
- the vertical acceptance tests pass;
- the complete PHPUnit suite passes;
- PHPStan level 8 reports zero errors;
- Builder reports zero diagnostics;
- governed generation is deterministic;
- `git diff --check` passes.

## Future work

Database-specific persistence must proceed in separate work packages.

Recommended sequence:

1. PDO adapter architecture;
2. SQL query compilation contracts;
3. SQL Server adapter;
4. PostgreSQL adapter;
5. MySQL adapter;
6. migration engine;
7. BaseModel 2.0 integration.

Each adapter must depend on WP-209 contracts. WP-209 Core must never depend on a concrete adapter.
