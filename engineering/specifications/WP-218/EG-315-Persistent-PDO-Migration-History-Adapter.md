---
id: EG-315
title: Persistent PDO Migration History Adapter
summary: Specifies the governed PDO-backed implementation of migration history persistence, including table identity, platform SQL compilation, hydration and failure semantics.
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
  - history
  - persistence
depends_on:
  - EG-313
  - EG-314
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-315 — Persistent PDO Migration History Adapter

## 1. Purpose

This specification governs WP-218 I3. It introduces a PDO-backed implementation of `MigrationHistoryStoreInterface` while preserving the immutable history model established by WP-217.

## 2. Scope

I3 introduces:

- `PdoMigrationHistoryTable`;
- `PdoMigrationHistorySql`;
- `PdoMigrationHistoryStore`;
- `InvalidPdoMigrationHistoryTableException`;
- `PdoMigrationHistoryStorageException`.

I3 SHALL NOT acquire migration locks, begin or commit transactions, execute migration operations, compose Installer services or register Runtime providers.

## 3. Table identity

The history table SHALL be represented independently from SQL text.

Table and optional schema identifiers SHALL:

- be trimmed;
- begin with a letter or underscore;
- contain only letters, digits and underscores;
- reject path syntax, separators, quotes, comments and statement delimiters.

The default table name SHALL be `sif_migration_history`.

Identifier quoting SHALL be platform-specific:

- PostgreSQL: double quotes;
- MySQL: backticks;
- SQL Server: brackets.

No caller-provided identifier SHALL be concatenated into SQL before validation and quoting.

## 4. Persistent columns

The physical history representation SHALL preserve:

- migration identifier;
- checksum including algorithm prefix;
- history status;
- recording timestamp in `DATE_ATOM` representation;
- optional migration version;
- optional batch identifier.

The migration identifier SHALL be the primary key.

I3 stores timestamps as canonical text. Temporal database specialization remains outside this increment so that hydration semantics remain identical across the three governed platforms.

## 5. Platform SQL compilation

`PdoMigrationHistorySql` SHALL compile deterministic SQL for PostgreSQL, MySQL and SQL Server.

PostgreSQL and MySQL SHALL use `CREATE TABLE IF NOT EXISTS`.

SQL Server SHALL guard creation with `OBJECT_ID` before `CREATE TABLE`.

Read, insert and delete operations SHALL use the same governed column set. Dynamic record values SHALL use prepared-statement parameters.

## 6. Initialization

`PdoMigrationHistoryStore` SHALL support explicit `initialize()` and optional lazy initialization.

Initialization SHALL be idempotent per store instance. A successful initialization SHALL not be repeated by later operations on the same instance.

When automatic initialization is disabled, the store SHALL execute only the requested history operation. This permits externally governed schema provisioning.

## 7. Contract behavior

The store SHALL implement `MigrationHistoryStoreInterface`:

- `history()` loads all records ordered by migration identifier;
- `find()` returns one record or `null`;
- `append()` persists the full immutable record;
- `remove()` deletes by migration identifier.

The store SHALL NOT introduce replacement, upsert or duplicate reconciliation semantics not present in the WP-217 contract.

## 8. Hydration and integrity

Persisted rows SHALL be reconstituted through the existing value objects:

- `MigrationId`;
- `MigrationChecksum`;
- `MigrationHistoryStatus`;
- `MigrationVersion`;
- `MigrationHistoryRecord`.

Invalid persisted data SHALL fail closed. The adapter SHALL NOT normalize or silently repair malformed identifiers, checksums, statuses, timestamps, versions or batches.

## 9. Failure semantics

PDO failures, statement preparation failures, execution failures and invalid persisted rows SHALL surface as `PdoMigrationHistoryStorageException`.

The original throwable SHALL be retained as the previous exception when one exists.

No exception SHALL expose DSNs, usernames, passwords or raw connection configuration.

## 10. Security and observability

Prepared statements SHALL be used for all record values.

Table identifiers SHALL be governed values rather than free SQL fragments.

Public error messages SHALL identify the failed history action and, where useful, the migration identifier, but SHALL not expose credentials.

## 11. Acceptance criteria

I3 is accepted when:

- all identifiers are validated and quoted per platform;
- SQL creation differs correctly across PostgreSQL, MySQL and SQL Server;
- initialization is idempotent per store instance;
- persisted rows hydrate into WP-217 immutable values;
- invalid rows fail closed;
- append and remove use governed parameters;
- no lock, transaction or migration operation behavior is introduced;
- PHPUnit, PHPStan and Builder validation pass in the governed repository.
