---
id: EG-241
title: Persistence Architecture
summary: Defines the storage-neutral persistence architecture for SIF, including repositories, transaction boundaries, connection abstractions, query contracts, mappers, storage adapters, and unit-of-work boundaries.
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
  - transaction
  - storage
  - architecture
depends_on:
  - EG-240
  - EG-232
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-241 — Persistence Architecture

## 1. Purpose

This specification defines the architectural boundary of the future SIF Persistence subsystem.

The subsystem SHALL provide stable, storage-neutral contracts for repositories, transactions, connection handling, query representation, data mapping, and storage adapters without coupling the Core to SQL Server, PostgreSQL, MySQL, SQLite, PDO, ODBC, an ORM, or a specific database schema.

WP-209-I1 is exclusively architectural. It introduces no production PHP code, migrations, SQL, database adapters, repositories, query builders, or model integration.

## 2. Architectural position

Persistence is an infrastructure boundary.

The intended dependency direction is:

```text
Application or module
        |
        v
Persistence contracts
        |
        v
Application-specific repository interfaces
        |
        v
Infrastructure adapters
        |
        v
Database, file, memory, remote service, or other storage
```

The Core SHALL define abstractions and stable value boundaries only.

Concrete adapters SHALL depend on the Core contracts. The Core SHALL never depend on adapters.

## 3. Primary design principles

### 3.1 Storage neutrality

The Persistence Core SHALL NOT know whether data is stored in:

- SQL Server;
- PostgreSQL;
- MySQL;
- SQLite;
- a document database;
- a key-value store;
- files;
- memory;
- a remote service;
- a queue-backed projection;
- another persistence technology.

No SQL dialect, PDO constant, ODBC behavior, connection string, table name, schema name, or database driver type SHALL appear in Core contracts.

### 3.2 Explicit boundaries

Persistence operations SHALL be explicit.

The architecture SHALL avoid:

- hidden global connections;
- ambient transactions;
- implicit unit-of-work registration;
- automatic model discovery;
- reflection-based persistence;
- static database state;
- magical query construction.

Any transaction, repository, mapper, or query dependency SHALL be passed explicitly through contracts.

### 3.3 Application-owned repositories

Repository interfaces SHOULD be owned by the application or module that defines the aggregate or model contract.

The Persistence Core MAY provide generic supporting abstractions, but it SHALL NOT force every application repository to expose CRUD methods.

Repositories SHOULD reflect application language and use cases.

Examples:

```text
CaseRepositoryInterface
DocumentRepositoryInterface
UserRepositoryInterface
```

rather than a universal mandatory repository with every possible operation.

### 3.4 Transaction neutrality

Transaction semantics SHALL be expressed through stable contracts.

The Core SHALL NOT assume:

- nested transactions are supported;
- savepoints exist;
- all storage engines are transactional;
- distributed transactions are available;
- rollback is always possible;
- read-only transactions behave identically across adapters.

Adapters SHALL declare or enforce their capabilities explicitly.

### 3.5 Mapping neutrality

Mapping between application objects and storage representations SHALL occur through explicit mapper contracts.

The Core SHALL NOT inspect arbitrary properties through reflection.

A mapper MAY:

- hydrate an application object;
- extract a storage-neutral record;
- normalize identifiers;
- map value objects;
- translate between naming conventions.

The mapper SHALL NOT own transactions or connection lifecycle.

### 3.6 Query neutrality

Query contracts SHALL represent intent, not SQL syntax.

The Core MAY define values such as:

- criteria;
- filters;
- sorting;
- pagination;
- projections;
- query specifications;
- result limits.

Concrete adapters translate these values into native SQL, API requests, file operations, or another technology-specific mechanism.

### 3.7 Failure transparency

Failures SHALL be represented with typed exceptions and stable categories.

The architecture SHOULD distinguish:

- connection acquisition failure;
- transaction failure;
- query execution failure;
- mapping failure;
- optimistic concurrency conflict;
- unsupported capability;
- repository-level not-found semantics where appropriate.

Adapters MAY preserve the original throwable as a cause but SHALL NOT expose driver-specific exceptions as the only public API.

## 4. Conceptual model

### 4.1 Repository

A repository provides access to an application-defined collection of domain or model objects.

A repository:

- exposes use-case-oriented methods;
- depends on persistence contracts;
- hides storage details;
- returns application-level objects or explicit result values;
- does not expose raw database handles.

The Core SHALL NOT require repositories to extend a common base class.

### 4.2 Connection

A connection abstraction represents an adapter-controlled access channel to a storage technology.

The Core contract SHOULD expose only operations required by higher-level persistence abstractions.

A connection SHALL NOT be confused with a global singleton or application service locator.

### 4.3 Connection manager

A connection manager resolves named or contextual connections explicitly.

It MAY support:

- default connection;
- named connection;
- read/write separation;
- tenant-aware resolution;
- connection capability inspection.

It SHALL NOT read global state implicitly.

### 4.4 Transaction manager

A transaction manager executes a callback within an explicit transaction boundary.

Conceptually:

```php
$result = $transactionManager->transactional(
    static function (): mixed {
        // explicit persistence operations
    },
);
```

The final signature SHALL be defined incrementally.

The contract SHALL define:

- commit behavior;
- rollback behavior;
- exception propagation;
- nested transaction policy;
- callback return preservation.

### 4.5 Unit of work

A unit of work coordinates a set of pending persistence changes.

It MAY track:

- new objects;
- changed objects;
- removed objects;
- identity map entries;
- expected versions.

The initial architecture SHALL treat Unit of Work as optional. Repository and transaction contracts must remain usable without it.

### 4.6 Query

A query is an immutable representation of retrieval intent.

A query MAY contain:

- criteria;
- ordering;
- pagination;
- projection;
- lock intent;
- metadata.

The Core SHALL NOT embed SQL strings in generic query objects.

### 4.7 Result set

A result set represents a stable collection or stream of mapped results.

The architecture SHOULD allow:

- eager lists;
- iterables;
- paginated results;
- cursors or streams through adapter-specific extensions.

The Core SHALL avoid forcing large result sets into memory.

### 4.8 Mapper

A mapper translates between storage-neutral records and application objects.

The architecture SHALL distinguish:

- object hydration;
- object extraction;
- identifier mapping;
- version mapping;
- optional change tracking.

### 4.9 Storage adapter

A storage adapter implements Core contracts for a specific technology.

Examples may include:

- PDO SQL adapter;
- SQL Server adapter;
- PostgreSQL adapter;
- MySQL adapter;
- in-memory adapter;
- file adapter.

Adapters MAY depend on vendor libraries. Core contracts SHALL NOT.

## 5. Proposed public boundaries

Later increments SHOULD define contracts conceptually equivalent to:

- `RepositoryInterface`;
- `ConnectionInterface`;
- `ConnectionManagerInterface`;
- `TransactionManagerInterface`;
- `UnitOfWorkInterface`;
- `QueryInterface`;
- `CriteriaInterface`;
- `MapperInterface`;
- `ResultSetInterface`;
- `PersistenceCapabilityInterface`.

Exact names and signatures SHALL be approved incrementally.

## 6. Repository policy

A generic repository contract MAY provide only minimal shared semantics, such as:

- identity of the managed type;
- capability metadata;
- optional query execution.

It SHALL NOT force universal methods such as:

```text
findAll
save
delete
paginate
count
```

unless a later specification demonstrates that those methods are stable across technologies and application models.

Application-specific repository interfaces remain preferred.

## 7. Transaction policy

Transactions SHALL be explicit and scoped.

The transaction manager SHALL:

- begin before callback execution;
- commit only after successful completion;
- rollback after a failure when supported;
- rethrow the original failure or a typed transaction failure;
- preserve the callback return value;
- document nested transaction behavior.

No transaction SHALL be started implicitly by reading from a repository.

## 8. Connection lifecycle

Connection creation, reuse, health checks, and closure belong to adapters and connection managers.

The Core SHALL not:

- call `new PDO`;
- read environment variables directly;
- construct DSNs;
- select ODBC drivers;
- open sockets;
- retry connections automatically without policy.

Connection configuration SHALL be supplied through explicit configuration values in a future integration layer.

## 9. Query and criteria policy

Queries and criteria SHALL be immutable.

The architecture SHOULD support common intent such as:

- equality and inequality;
- comparison;
- membership;
- null checks;
- string matching;
- ordering;
- pagination;
- projections.

The Core SHALL not guarantee that every adapter supports every operation.

Unsupported operations SHALL fail explicitly through capability checks or typed exceptions.

## 10. Concurrency and versioning

The architecture SHALL allow optimistic concurrency without requiring it.

A later increment MAY define:

- version tokens;
- expected version values;
- conflict exceptions;
- compare-and-swap semantics.

No adapter may silently ignore an explicitly requested concurrency check.

## 11. Persistence and Audit

Persistence adapters MAY emit Audit events or listen to application events, but the Persistence Core SHALL NOT depend on Audit.

The dependency direction SHALL remain:

```text
Application integration
        -> Persistence contracts
        -> Audit contracts
```

or:

```text
Application integration
        -> Persistence adapter
        -> Audit service
```

Audit and Persistence SHALL remain independently usable.

## 12. Persistence and Execution Context

Persistence operations MAY receive `ExecutionContextInterface` explicitly when needed for:

- tenant resolution;
- actor-aware policies;
- tracing;
- correlation;
- audit integration.

The Core SHALL NOT retrieve context from global state.

Context association SHALL remain explicit in method arguments or operation objects.

## 13. Security and confidentiality

Persistence adapters SHALL consider:

- parameter binding;
- injection prevention;
- credential handling;
- encryption in transit;
- secret redaction;
- tenant isolation;
- least-privilege access;
- safe error reporting.

The Core SHALL not store credentials or raw connection strings in diagnostics.

## 14. Explicit exclusions

WP-209-I1 does not define or implement:

- SQL syntax;
- query builders;
- PDO wrappers;
- ODBC wrappers;
- database connections;
- migrations;
- schema management;
- ORM behavior;
- Active Record;
- BaseModel integration;
- automatic CRUD;
- lazy loading;
- relation mapping;
- identity map;
- caching;
- distributed transactions;
- retry policies;
- persistence events;
- database-specific adapters.

## 15. Increment plan

WP-209 SHOULD proceed through small governed increments:

1. **WP-209-I1** — architecture;
2. **WP-209-I2** — connection and transaction contracts;
3. **WP-209-I3** — query, criteria, sorting, and pagination values;
4. **WP-209-I4** — mapping and result-set contracts;
5. **WP-209-I5** — repository and unit-of-work contracts;
6. **WP-209-I6** — capability model and typed failure taxonomy;
7. **WP-209-I7** — in-memory reference adapter;
8. **WP-209-I8** — vertical reference integration and product completion.

Database-specific adapters SHALL be separate work packages after WP-209 is complete.

## 16. Acceptance criteria

WP-209-I1 is accepted when:

- storage neutrality is explicit;
- repositories remain application-owned;
- transaction boundaries are explicit;
- connection lifecycle is adapter-owned;
- mapping avoids reflection;
- query contracts represent intent rather than SQL;
- Unit of Work remains optional;
- Context integration remains explicit;
- Audit remains independent;
- governed metadata validation passes;
- Builder diagnostics remain zero;
- governed generation is deterministic.
