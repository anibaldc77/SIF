---
id: EG-325
title: Prepared Statement Execution and Result Sets
summary: Defines typed PDO prepared-statement execution, parameter binding, immutable row adaptation and safe failure translation for compiled persistence queries.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-31
updated: 2026-07-31
work_package: WP-219
tags:
  - foundation
  - persistence
  - pdo
  - prepared-statements
  - result-set
  - execution
depends_on:
  - EG-321
  - EG-322
  - EG-323
  - EG-324
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# Prepared Statement Execution and Result Sets

## Purpose

Define the first side-effecting layer of WP-219: execution of a previously compiled query through PDO, with explicit parameter binding, deterministic row adaptation and safe error boundaries.

## Normative requirements

1. The executor MUST accept a `PdoCompiledQuery` and MUST NOT accept raw parameter arrays or unvalidated SQL fragments.
2. Statement preparation MUST use the PDO instance exposed by `PdoPersistenceConnection`.
3. Every `PdoSqlParameter` MUST be bound through `PDOStatement::bindValue()` using its canonical placeholder, value and explicit PDO type.
4. Parameter values MUST NOT be interpolated into SQL or included in diagnostic summaries.
5. A bind failure MUST stop execution before `PDOStatement::execute()`.
6. Statement execution MUST fail closed when PDO returns `false` or throws a `PDOException`.
7. Query rows MUST be fetched as associative arrays and adapted to immutable `StorageRecord` instances.
8. Invalid row keys or values MUST fail closed rather than being silently coerced.
9. Adapted records MUST be returned through the existing provider-neutral `ResultSet` implementation.
10. The execution result MUST expose record count and affected-row count without exposing row values.
11. The cursor MUST be closed in a `finally` boundary after every prepared statement attempt.
12. Cleanup failure MUST NOT replace the primary execution result or exception.
13. Preparation, execution and adaptation failures MUST be translated to `PdoStatementExecutionException` with a safe public message and preserved original cause where one exists.
14. The executor MUST NOT manage transactions, repositories, entity mapping or Unit of Work behavior.

## Output model

`PdoQueryResult` contains:

- `ResultSet<StorageRecord>`;
- non-negative affected-row count;
- safe summary containing only record and affected-row counts.

## Deferred scope

Connection-level transaction adapters, repository mapping, writes, generated identifiers and Unit of Work coordination remain deferred to WP-219 I6 and I7.
