---
id: EG-314
title: PDO Connection and Platform Value Model
summary: Specifies the immutable PDO migration connection reference, canonical platform identity, ownership policy and capability profiles used by PDO-backed migration adapters.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-30
updated: 2026-07-30
work_package: WP-218
tags:
  - foundation
  - database
  - migrations
  - pdo
  - connection
  - capabilities
depends_on:
  - EG-313
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-314 — PDO Connection and Platform Value Model

## 1. Purpose

This specification governs the first executable increment of WP-218. It establishes immutable PDO connection metadata and platform capability values before persistent history, locks, transaction coordination or SQL execution are introduced.

## 2. Scope

I2 introduces:

- `PdoMigrationConnectionName`;
- `PdoMigrationConnectionOwnership`;
- `PdoMigrationPlatform`;
- `PdoMigrationCapabilities`;
- `PdoMigrationConnection`;
- typed validation exceptions rooted in `PdoMigrationException`.

I2 SHALL NOT execute SQL, create history tables, acquire locks, begin transactions, register runtime providers or mutate Installer plans.

## 3. Connection reference

`PdoMigrationConnection` SHALL be an immutable reference containing:

- an injected `PDO` object;
- a canonical connection name;
- an explicit platform;
- an explicit ownership policy;
- an immutable capability profile.

The reference SHALL NOT contain a DSN, username, password or credential factory. Its public summary SHALL expose only safe metadata.

The declared platform SHALL equal the platform declared by the capability profile. A mismatch SHALL fail during construction.

## 4. Connection name

Connection names SHALL:

- be trimmed;
- remain case-sensitive;
- be non-empty;
- use only letters, numbers, dots, underscores, colons and hyphens;
- reject path syntax and whitespace.

The name identifies a governed migration connection. It is not a DSN and SHALL NOT encode credentials.

## 5. Ownership policy

Connection ownership SHALL use the closed values:

- `external`: the application owns the injected PDO instance;
- `adapter`: the adapter composition owns the connection lifecycle.

No implicit shared or inferred ownership state is permitted.

I2 records ownership only. Connection closing or replacement behavior remains outside this increment.

## 6. Platform identity

The canonical initial platforms SHALL be:

- `postgresql`;
- `mysql`;
- `sqlserver`.

Driver aliases SHALL normalize deterministically:

| Driver or alias | Canonical platform | Canonical PDO driver |
|---|---|---|
| `pgsql`, `postgres`, `postgresql` | `postgresql` | `pgsql` |
| `mysql` | `mysql` | `mysql` |
| `sqlsrv`, `mssql`, `sqlserver` | `sqlserver` | `sqlsrv` |

Unsupported values SHALL fail closed. SQLite is not claimed by WP-218.

## 7. Capability profile

A capability profile SHALL declare:

- platform;
- transaction support;
- transactional DDL expectation;
- savepoint support;
- atomic history/schema coupling;
- lock mechanism;
- lock scope;
- lock timeout support;
- supported migration directions.

Capabilities SHALL be immutable and internally consistent.

The following contradictions SHALL be rejected:

- transactional DDL without transaction support;
- savepoints without transaction support;
- atomic history/schema coupling without transaction support;
- atomic history/schema coupling without transactional DDL;
- empty, duplicate or untyped directions;
- empty or malformed lock mechanisms;
- lock scopes outside `session` and `transaction`.

## 8. Initial governed profiles

### 8.1 PostgreSQL

The initial PostgreSQL profile declares:

- transactions supported;
- transactional DDL expected;
- savepoints supported;
- atomic history/schema coupling supported;
- advisory lock mechanism;
- session lock scope;
- bounded lock timeout support;
- up and down directions.

### 8.2 MySQL

The initial MySQL profile declares:

- transactions supported;
- transactional DDL not generally guaranteed;
- savepoints supported;
- atomic history/schema coupling not guaranteed;
- named lock mechanism;
- session lock scope;
- bounded lock timeout support;
- up and down directions.

### 8.3 SQL Server

The initial SQL Server profile declares:

- transactions supported;
- transactional DDL expected for supported operations;
- savepoints supported;
- atomic history/schema coupling supported for supported operations;
- application lock mechanism;
- session lock scope;
- bounded lock timeout support;
- up and down directions.

These profiles are declarations for adapter composition. They do not by themselves prove vendor conformance; live conformance evidence remains required in later increments.

## 9. Security and diagnostics

Public summaries SHALL NOT expose:

- DSNs;
- credentials;
- arbitrary PDO attributes;
- raw SQL;
- connection options containing secrets.

Validation exceptions SHALL describe the invalid policy state without serializing the PDO object.

## 10. Acceptance criteria

I2 is accepted when:

1. connection identity is immutable and validated;
2. ownership is explicit and closed;
3. platform aliases normalize deterministically;
4. unsupported platforms fail closed;
5. capability contradictions fail during construction;
6. connection and capability platforms must match;
7. summaries expose safe metadata only;
8. no PDO statement is prepared or executed;
9. focused unit tests cover valid and invalid states;
10. PHPStan level 8 reports zero errors;
11. repository Builder validation reports zero diagnostics.

## 11. Deferred work

The following remain deferred:

- persistent migration history: I3;
- vendor lock adapters: I4;
- transaction coordination: I5;
- SQL operation handling: I6;
- composition and Installer integration: I7;
- Runtime integration and completion: I8.
