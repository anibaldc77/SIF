---
id: EG-324
title: PDO Platform Query Compilers
summary: Defines deterministic compilation of the immutable PDO query AST into PostgreSQL, MySQL and SQL Server SELECT statements with validated identifiers and preserved bound parameters.
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
  - sql
  - compiler
  - postgresql
  - mysql
  - sqlserver
depends_on:
  - EG-321
  - EG-322
  - EG-323
  - EG-301
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# PDO Platform Query Compilers

## Purpose

Define side-effect-free compilers that transform the validated SELECT query AST into executable SQL text for PostgreSQL, MySQL and SQL Server while preserving the original bound-parameter model.

## Normative requirements

1. Compilation MUST be deterministic and MUST NOT access a database or execute a statement.
2. Compilers MUST accept only the immutable AST introduced by EG-323.
3. Every identifier MUST be rendered through `PdoSqlIdentifier::quoted()` using the selected platform profile.
4. Empty projection MUST compile to `*`; the wildcard MUST NOT be represented as a validated identifier.
5. Comparison, null, IN/NOT IN and LIKE predicates MUST compile without interpolating parameter values.
6. Predicate placeholders MUST be preserved exactly as produced by translation.
7. LIKE predicates MUST render an explicit `ESCAPE` clause.
8. Sort direction MUST originate from the provider-neutral `SortDirection` enum.
9. PostgreSQL and MySQL pagination MUST render `LIMIT <limit> OFFSET <offset>`.
10. SQL Server pagination MUST render `OFFSET <offset> ROWS FETCH NEXT <limit> ROWS ONLY` and MUST reject pagination without an explicit `ORDER BY` clause.
11. Platform-specific compilers MUST reject construction with a mismatched platform profile.
12. Compilation results MUST expose SQL text and the unchanged parameter bag, and diagnostic summaries MUST NOT expose parameter values.
13. The compiler factory MUST select exactly one compiler for PostgreSQL, MySQL or SQL Server.

## Output model

`PdoCompiledQuery` contains:

- compiled SQL text;
- immutable `PdoSqlParameterBag`;
- safe summary with SQL text and parameter count.

## Deferred scope

Prepared-statement execution, row adaptation, mutation compilation, transaction orchestration and repository behavior remain deferred to WP-219 I5 and later increments.
