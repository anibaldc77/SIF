---
id: REVIEW-WP-219-I4
title: WP-219 I4 Implementation Review
summary: Reviews deterministic PostgreSQL, MySQL and SQL Server compilation of the immutable PDO persistence query AST.
status: Draft for Review
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Architecture Board
created: 2026-07-31
updated: 2026-07-31
work_package: WP-219
tags:
  - review
  - persistence
  - pdo
  - compiler
  - sql
depends_on:
  - EG-324
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-219 I4 Implementation Review

## Scope reviewed

- immutable compiled-query result;
- compiler contract and common compilation pipeline;
- PostgreSQL compiler;
- MySQL compiler;
- SQL Server compiler;
- compiler factory;
- identifier quoting;
- predicate, sorting and pagination rendering;
- unit tests without database access.

## Findings

1. Compilation is side-effect free and does not interact with PDO or a database.
2. SQL values are represented only by placeholders; no bound value is interpolated.
3. PostgreSQL and MySQL differ only in identifier quoting while sharing explicit limit/offset pagination.
4. SQL Server uses standards-compatible offset/fetch pagination and rejects nondeterministic paginated queries without ordering.
5. The result preserves the parameter bag produced by the AST.
6. The common compiler rejects unknown predicate implementations rather than silently rendering unsafe SQL.
7. Existing provider-neutral persistence contracts and WP-219 I2/I3 value objects remain unchanged.

## Decision

The implementation is suitable as the compilation layer for prepared-statement execution and result adaptation planned for WP-219 I5, subject to repository validation and the complete Windows test suite.
