---
id: EG-318
title: SQL Migration Operation Model and PDO Handler
summary: Specifies immutable SQL migration operations, deterministic registration and PDO execution through the WP-217 migration operation handler contract.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-31
updated: 2026-07-31
work_package: WP-218
tags:
  - foundation
  - database
  - migrations
  - pdo
  - sql
depends_on:
  - EG-313
  - EG-314
  - EG-315
  - EG-316
  - EG-317
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-318 — SQL Migration Operation Model and PDO Handler

## 1. Purpose

This specification governs WP-218 I6. It connects declarative, immutable SQL operations to `MigrationOperationHandlerInterface` without changing the provider-neutral migration descriptor introduced by WP-217.

## 2. Scope

I6 introduces:

- `PdoMigrationSqlStatement`;
- `PdoMigrationSqlOperation`;
- `PdoMigrationSqlOperationCatalog`;
- `PdoMigrationSqlOperationHandler`;
- SQL-model and execution-specific typed exceptions.

I6 SHALL NOT generate SQL, infer schema changes, manage transactions or locks, compose Installer services or register Runtime providers.

## 3. Statement model

A SQL statement SHALL be immutable and non-empty. Null bytes SHALL be rejected. Dynamic values SHALL be represented only as validated named parameters and SHALL be passed separately to PDO.

Parameter names SHALL match `[A-Za-z_][A-Za-z0-9_]*`. Parameter values SHALL be limited to scalar values or `null`. Summaries MAY expose SQL text and parameter names, but SHALL NOT expose parameter values.

## 4. Operation model

Each SQL operation SHALL be identified by a `MigrationId`. It SHALL contain one or more ordered `up` statements and MAY contain ordered `down` statements.

An operation with no `down` statements SHALL be considered irreversible. Statement order SHALL be preserved exactly.

## 5. Catalog

The catalog SHALL register operations deterministically by migration identifier. Duplicate identifiers and non-operation values SHALL fail closed with a typed exception. Registration order SHALL be preserved for inspection.

The catalog SHALL NOT discover files, inspect classes or mutate migration descriptors.

## 6. Handler selection

`PdoMigrationSqlOperationHandler::supports()` SHALL return `true` only when the catalog contains an operation with the descriptor identifier. The handler SHALL therefore remain compatible with the handler selection rules of `MigrationExecutor`.

## 7. Execution semantics

For the selected direction, statements SHALL execute sequentially using `PDO::prepare()` and `PDOStatement::execute()` with named parameters. The handler SHALL close each cursor after successful execution.

Transaction, rollback and lock behavior SHALL remain the responsibility of the WP-217 executor and the I4/I5 adapters.

An unavailable reverse operation SHALL return `IRREVERSIBLE_MIGRATION`. Successful completion of every statement SHALL return `MigrationOperationResult::success()`.

## 8. Failure semantics

Prepare or execute rejection SHALL fail closed with `PdoMigrationSqlExecutionException`. PDO and unexpected execution failures SHALL be translated to the typed exception while preserving the original cause.

The handler SHALL NOT continue to later statements after a failure.

## 9. Security boundaries

I6 SHALL NOT interpolate parameter values into SQL, SHALL NOT accept unsafe parameter names and SHALL NOT log parameter values through summaries. SQL text is trusted migration input and is not generated from user input by this adapter.

## 10. Acceptance criteria

I6 is acceptable when:

- invalid SQL and unsafe parameter names are rejected;
- up statements are mandatory and ordered;
- duplicate operation identifiers are rejected;
- handler support is catalog-driven;
- parameters are passed separately to PDO;
- irreversible down execution returns the governed failure code;
- PDO failures preserve their original cause;
- PHPUnit, PHPStan, Builder validation and repository checks pass.
