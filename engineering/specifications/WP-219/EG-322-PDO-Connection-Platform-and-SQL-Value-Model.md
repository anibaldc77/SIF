---
id: EG-322
title: PDO Persistence Connection, Platform and SQL Value Model
summary: Defines side-effect-free PDO persistence connection metadata, supported platform capabilities, validated SQL identifiers and typed bound parameters.
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
  - value-objects
depends_on:
  - EG-321
  - EG-301
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# PDO Persistence Connection, Platform and SQL Value Model

## Purpose

Establish the immutable and explicit values used by later PDO persistence compilation and execution increments without executing SQL during construction.

## Normative requirements

1. Supported persistence platforms MUST be PostgreSQL, MySQL and SQL Server and MUST normalize their canonical PDO driver aliases.
2. A connection MUST implement the provider-neutral `ConnectionInterface`, retain an explicit ownership policy and reject a capability profile for a different platform.
3. Closing a connection MUST make subsequent PDO access fail. Externally owned PDO handles MUST NOT be destroyed by the adapter.
4. Capability profiles MUST disclose transaction, savepoint, returning, pagination, parameter-limit and query-operator support.
5. SQL identifiers MUST be validated as one or more canonical segments before platform-specific quoting. Arbitrary SQL fragments MUST NOT be accepted as identifiers.
6. SQL parameters MUST use named placeholders, an explicit or safely inferred PDO type and immutable values.
7. Parameter collections MUST reject duplicate names and MUST NOT expose bound values through summaries.
8. Construction of every value in this increment MUST remain free of database I/O.

## Deferred scope

Query AST translation, SQL statement compilation, prepared-statement execution and transaction orchestration are deferred to later WP-219 increments.
