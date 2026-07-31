---
id: EG-317
title: PDO Transaction Coordination
summary: Defines the PDO migration transaction manager, external transaction participation policy, savepoint behavior, state divergence handling and failure preservation for WP-218 I5.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Engineering
created: 2026-07-31
updated: 2026-07-31
work_package: WP-218
tags:
  - migrations
  - pdo
  - transactions
  - savepoints
depends_on:
  - EG-313
  - EG-314
  - EG-315
  - EG-316
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-317 — PDO Transaction Coordination

## 1. Purpose

This increment connects the neutral `MigrationTransactionManagerInterface` from WP-217 to PDO while preserving explicit platform capabilities and connection ownership boundaries.

## 2. Scope

I5 introduces:

- a PDO migration transaction manager;
- an explicit policy for externally active transactions;
- savepoint-based participation when supported and requested;
- local transaction state inspection;
- detection of PDO state divergence;
- typed transaction failures that preserve the original PDO exception.

I5 does not introduce SQL migration operations, Installer composition, Runtime registration or automatic execution during boot.

## 3. Transaction capability

`supportsTransactions()` SHALL reflect the declared `PdoMigrationCapabilities` profile. A profile that does not support transactions SHALL fail closed when `begin()` is requested.

The manager SHALL not infer transaction support solely from the presence of PDO methods.

## 4. Owned transaction

When PDO reports no active transaction, `begin()` SHALL request a new PDO transaction. The manager becomes the owner only after PDO confirms success.

For a manager-owned transaction:

- `commit()` SHALL invoke PDO commit;
- `rollBack()` SHALL invoke PDO rollback;
- local state SHALL return to idle only after database confirmation;
- a missing database transaction while local state is active SHALL be treated as divergence.

## 5. External transaction policy

An already active PDO transaction SHALL be treated as externally owned because the manager did not create it.

The policy is closed to:

- `reject`: fail without modifying the external transaction;
- `savepoint`: participate through a governed savepoint.

`reject` is the default.

The manager SHALL never commit or roll back the complete external transaction.

## 6. Savepoint participation

Savepoint participation SHALL require both:

- the explicit `savepoint` policy;
- a capability profile that declares savepoint support.

The savepoint identifier SHALL be validated as a bounded SQL identifier and SHALL never contain dynamic user SQL.

The lifecycle is:

- begin: `SAVEPOINT <identifier>`;
- commit: `RELEASE SAVEPOINT <identifier>`;
- rollback: `ROLLBACK TO SAVEPOINT <identifier>` followed by `RELEASE SAVEPOINT <identifier>`.

The surrounding external transaction SHALL remain active.

## 7. State model

The local state is closed to:

- `idle`;
- `owned`;
- `savepoint`.

Nested `begin()` calls on the same manager SHALL be rejected. Commit and rollback without an active manager state SHALL be rejected.

## 8. Failure behavior

PDO exceptions SHALL be translated to `PdoMigrationTransactionException` with the original exception retained as the previous cause.

A failed commit, rollback or savepoint operation SHALL not silently reset local state. This preserves evidence that cleanup remains unresolved.

The existing `MigrationExecutor` remains responsible for preserving a primary migration failure when transaction rollback also fails.

## 9. Security and diagnostics

Transaction diagnostics SHALL expose state and policy only. DSNs, credentials and unrestricted SQL SHALL not be included.

## 10. Verification requirements

Tests SHALL prove:

- owned begin and commit;
- owned rollback;
- default rejection of external transactions;
- savepoint commit without external commit;
- savepoint rollback without external rollback;
- nested begin rejection;
- state divergence failure;
- unsupported capability failure;
- policy and identifier validation;
- PDO cause preservation.

## 11. Acceptance criteria

I5 is accepted when Composer validation, PHPUnit, PHPStan level 8, governed artifact generation, repository validation and whitespace checks all pass.
