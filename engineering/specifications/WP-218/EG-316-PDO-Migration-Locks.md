---
id: EG-316
title: PDO Migration Locks
summary: Specifies native session-scoped migration locks for PostgreSQL, MySQL and SQL Server through the WP-217 migration lock contract.
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
  - locking
depends_on:
  - EG-313
  - EG-314
  - EG-315
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-316 — PDO Migration Locks

## 1. Purpose

This specification governs WP-218 I4. It introduces PDO-backed implementations of `MigrationLockInterface` using the native session-scoped locking primitives of PostgreSQL, MySQL and SQL Server.

## 2. Scope

I4 introduces:

- `PdoMigrationLockResource`;
- `PdoMigrationLockTimeout`;
- `PdoMigrationLockSql`;
- `PdoMigrationLock`;
- lock-specific typed exceptions.

I4 SHALL NOT begin, commit or roll back transactions, execute migration operations, compose Installer services or register Runtime providers.

## 3. Resource identity

The lock resource SHALL be immutable, non-empty, bounded to 128 bytes and restricted to a portable identifier alphabet. The default resource SHALL be `sif:migrations`.

PostgreSQL advisory locks SHALL use two deterministic signed 32-bit keys derived from the SHA-256 digest of the resource. MySQL and SQL Server SHALL receive the validated resource text directly through bound parameters.

## 4. Timeout model

Timeout SHALL be represented in milliseconds. Negative values SHALL be rejected. Platform adaptation SHALL preserve fail-fast behavior:

- PostgreSQL uses `pg_try_advisory_lock` and therefore does not wait;
- MySQL converts milliseconds to whole seconds using ceiling semantics;
- SQL Server passes milliseconds to `sp_getapplock`.

## 5. Platform primitives

The adapter SHALL use:

- PostgreSQL: `pg_try_advisory_lock` and `pg_advisory_unlock`;
- MySQL: `GET_LOCK` and `RELEASE_LOCK`;
- SQL Server: `sp_getapplock` and `sp_releaseapplock` with `Session` ownership.

No lock table SHALL be created.

## 6. Ownership semantics

The owner supplied by `MigrationLockInterface` SHALL be tracked locally only after database confirmation. Re-entrant acquisition through the same adapter instance SHALL return `false`. Release by a different owner SHALL be ignored. Local ownership SHALL be cleared only after the database confirms release.

## 7. Failure semantics

PDO failures SHALL be translated to `PdoMigrationLockException` with the original cause retained. A denied or timed-out acquisition SHALL return `false`. A release that is not confirmed by the database SHALL fail closed with a typed exception.

## 8. Security and observability

All dynamic values SHALL be bound parameters. SQL SHALL be selected solely from the closed platform model. Credentials and DSNs SHALL never appear in exception messages or summaries.

## 9. Acceptance criteria

I4 is accepted when:

- each supported platform compiles its native lock SQL;
- resource and timeout values are validated;
- acquisition and release results are interpreted correctly;
- local owner state follows confirmed database state;
- tests cover success, denial, owner mismatch and deterministic PostgreSQL keys;
- PHPStan level 8 and governed repository validation pass.
