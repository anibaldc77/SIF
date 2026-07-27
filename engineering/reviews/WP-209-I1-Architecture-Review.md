---
id: WP-209-I1-REVIEW
title: WP-209-I1 Persistence Architecture Review
summary: Reviews the storage-neutral persistence architecture, repository ownership, transaction boundaries, connection abstractions, mapping, query intent, and adapter separation.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Architecture Board
created: 2026-07-27
updated: 2026-07-27
work_package: WP-209
tags:
  - foundation
  - persistence
  - architecture
  - repository
  - transaction
  - review
depends_on:
  - EG-241
  - EG-240
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-209-I1 — Persistence Architecture Review

## Scope

WP-209-I1 defines the architectural boundary of the future Persistence subsystem.

It adds no production PHP code, SQL, migrations, connections, adapters, repositories, query builders, ORM behavior, or model integration.

## Reviewed decisions

The architecture establishes that:

- Persistence Core is storage-neutral;
- SQL Server, PostgreSQL, MySQL, PDO, and ODBC remain adapter concerns;
- repositories are primarily application-owned;
- transaction boundaries are explicit;
- connections are resolved through explicit managers;
- queries represent intent rather than SQL;
- mapping uses explicit contracts rather than reflection;
- Unit of Work is optional;
- capabilities are declared explicitly;
- unsupported operations fail predictably;
- Context integration remains explicit;
- Audit remains independent;
- database-specific adapters are deferred to later work packages.

## Dependency review

The proposed direction is valid:

```text
Application or module
        -> Persistence contracts
        -> Application repository interfaces
        -> Infrastructure adapters
        -> Storage technology
```

The inverse dependency is prohibited.

## Repository review

Avoiding a mandatory universal CRUD repository is appropriate.

Application-specific repository interfaces preserve use-case language and prevent infrastructure methods from becoming part of the domain API.

A minimal generic repository contract may still be introduced later for shared metadata or capabilities, but it must not dictate application behavior.

## Transaction review

The callback-based transaction boundary is a suitable cross-adapter abstraction provided that later contracts define:

- return-value preservation;
- rollback behavior;
- nested transaction policy;
- capability differences;
- exception propagation.

## Query review

Immutable criteria and query values are appropriate as long as they represent retrieval intent and do not embed SQL syntax.

Adapter capability checks are required because not every storage technology supports every filter, lock, projection, or pagination mode.

## Risk review

Primary risks are:

1. over-generalizing repositories;
2. leaking SQL concepts into Core contracts;
3. introducing ambient transactions;
4. coupling mapping to reflection or BaseModel;
5. assuming every adapter supports nested transactions;
6. forcing Unit of Work on simple adapters;
7. exposing driver exceptions directly;
8. coupling Persistence to Audit or Context global state.

The architecture mitigates these risks through explicit boundaries, adapter-owned behavior, optional capabilities, and dependency inversion.

## Compatibility review

No changes are required to:

- Runtime;
- Event Dispatcher;
- Observation;
- Execution Context;
- Audit;
- Configuration;
- Environment;
- Capability Registry.

The proposal is additive and compatible with PHP 8.2.

## Recommendation

Approve WP-209-I1.

Continue with WP-209-I2, limited to connection and transaction contracts, typed transaction states, explicit callback execution, and deterministic in-memory test doubles.

WP-209-I2 should not yet implement SQL, PDO, ODBC, repositories, query builders, mappers, Unit of Work, or database adapters.
