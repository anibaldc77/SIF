---
id: REVIEW-WP-219-I3
title: WP-219 I3 Implementation Review
summary: Reviews the immutable SQL query AST and translation of provider-neutral persistence criteria delivered by WP-219 I3.
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
  - ast
  - criteria
depends_on:
  - EG-323
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-219 I3 Implementation Review

## Scope reviewed

- immutable SELECT query AST;
- validated projection, source, predicate and sort identifiers;
- deterministic parameter naming;
- comparison, null, IN/NOT IN and LIKE translation;
- wildcard escaping;
- pagination translation;
- parameter aggregation;
- unit tests without database I/O.

## Findings

1. Translation remains isolated from platform-specific rendering and PDO execution.
2. No provider-neutral field is copied into an executable SQL fragment without identifier validation.
3. Collection predicates retain one bound value per placeholder and cannot create an empty `IN ()` expression.
4. LIKE semantics distinguish framework wildcards from user data through explicit escaping.
5. The empty projection state is structural and does not weaken identifier validation by treating `*` as a normal identifier.
6. Parameter names are repeatable for the same query order and unique inside each translation.
7. Existing persistence contracts remain unchanged.

## Decision

The implementation is suitable as the input model for the PostgreSQL, MySQL and SQL Server compilers planned for WP-219 I4, subject to repository validation and the complete Windows test suite.
