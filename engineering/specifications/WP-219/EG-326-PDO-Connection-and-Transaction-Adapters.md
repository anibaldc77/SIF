---
id: EG-326
title: PDO Connection and Transaction Adapters
summary: Defines governed PDO transaction coordination for persistence, including owned transactions, explicit external-transaction policy, savepoint participation and safe failure boundaries.
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
  - transactions
  - savepoints
depends_on:
  - EG-321
  - EG-322
  - EG-325
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# PDO Connection and Transaction Adapters

## Purpose

Define the provider-specific implementation of the existing `TransactionManagerInterface` using the governed PDO persistence connection and capability profile.

## Normative requirements

1. Transaction coordination MUST use `PdoPersistenceConnection` and MUST fail when transaction capability is unavailable.
2. A manager-owned scope MUST call `beginTransaction()`, invoke the operation, and then commit or roll back according to the outcome.
3. Nested manager scopes MUST be rejected.
4. An already-active external PDO transaction MUST be rejected by default.
5. Participation in an external transaction MAY occur only under the explicit `savepoint` policy and only when savepoints are supported.
6. Savepoint participation MUST release the savepoint on success and roll back only to the savepoint on failure.
7. The manager MUST NOT commit or completely roll back an external transaction.
8. Transaction state and depth MUST implement the provider-neutral contract.
9. PDO failures MUST be translated to safe typed exceptions while preserving the original cause.
10. Public diagnostics MUST NOT expose DSNs, credentials, bound values or driver messages.

## Deferred scope

Repositories, write compilation, generated identifiers, mapping and Unit of Work coordination remain deferred to WP-219 I7.
