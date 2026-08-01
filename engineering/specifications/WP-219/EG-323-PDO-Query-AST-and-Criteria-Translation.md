---
id: EG-323
title: PDO Query AST and Criteria Translation
summary: Defines the immutable provider-independent SQL query AST and deterministic translation of SIF persistence queries into validated identifiers, predicates and bound parameters.
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
  - query
  - ast
  - criteria
depends_on:
  - EG-321
  - EG-322
  - EG-301
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# PDO Query AST and Criteria Translation

## Purpose

Define a side-effect-free intermediate representation between the provider-neutral persistence query model and the platform-specific SQL compilers.

## Normative requirements

1. Translation MUST accept the existing provider-neutral `Query` model and MUST NOT execute SQL or access a database.
2. Table, projection, criterion and sort fields MUST become validated `PdoSqlIdentifier` values. Raw SQL fragments MUST NOT enter the AST through provider-neutral field names.
3. Flat `QueryCriteria` values MUST preserve their existing conjunction semantics and therefore MUST translate into an ordered SQL `AND` conjunction.
4. Equality and relational operators MUST translate into typed comparison predicates.
5. `IS NULL` and `IS NOT NULL` MUST translate without bound parameters.
6. `IN` and `NOT IN` MUST produce one uniquely named parameter for every collection element and MUST reject empty collections.
7. `contains`, `starts_with` and `ends_with` MUST translate into LIKE predicates whose user-provided percent, underscore and escape characters are escaped before framework wildcards are added.
8. Empty provider-neutral projection MUST represent all columns structurally; the wildcard MUST NOT be accepted as an identifier.
9. Pagination MUST translate from page/per-page semantics into immutable limit/offset values.
10. The AST and its collections MUST be immutable and MUST expose the complete parameter bag without exposing parameter values through diagnostic summaries.
11. Parameter names MUST be deterministic, canonical and unique within one translated query.

## AST structure

The increment defines:

- select source and projection;
- ordered conjunction of predicates;
- comparison, null, set-membership and LIKE predicates;
- ordered sort terms;
- limit/offset pagination;
- deterministic parameter-name generation.

## Deferred scope

Platform-specific SQL rendering, statement compilation, prepared-statement execution, result adaptation and transaction orchestration remain deferred to WP-219 I4 and later increments.
