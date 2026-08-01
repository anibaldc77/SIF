---
id: EG-327
title: PDO Repositories, Mappers and Unit of Work
summary: Defines governed PDO repositories, composite record keys, mapper integration, repository registration and transaction-bound Unit of Work coordination.
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
  - repositories
  - mappers
  - unit-of-work
depends_on:
  - EG-321
  - EG-322
  - EG-323
  - EG-324
  - EG-325
  - EG-326
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# PDO Repositories, Mappers and Unit of Work

## Purpose

Define the provider-specific repository and Unit of Work layer that composes the provider-neutral persistence contracts with the PDO query, compiler, execution and transaction components delivered by WP-219 I2 through I6.

## Normative requirements

1. PDO repositories MUST implement the existing read and write repository contracts.
2. Object mapping MUST continue to use `MapperInterface`; PDO adapters MUST NOT introduce a competing entity mapping contract.
3. Repository table names, key columns and writable columns MUST use validated SQL identifiers.
4. Composite identifiers MUST be represented by an immutable key value and MUST compile to ordered bound predicates.
5. `findById()` MUST be supported only for single-column identifiers; composite repositories MUST expose explicit key lookup.
6. Query execution MUST translate the canonical `Query`, compile it for the selected platform and execute it using prepared statements.
7. Write operations MUST use bound parameters and MUST NOT interpolate record values into SQL.
8. A repository registry MUST resolve managed objects deterministically and reject duplicate managed types.
9. The PDO Unit of Work MUST coordinate all registered changes inside one provider-neutral transaction boundary.
10. New and dirty objects MUST be saved before removed objects are deleted.
11. A successful commit MUST clear tracked changes; a failure MUST preserve the failure state defined by the existing Unit of Work contract.
12. Diagnostics MUST NOT expose record values, credentials or driver messages.

## Deferred scope

Runtime registration, service-provider composition, application capability publication and product completion remain deferred to WP-219 I8.
