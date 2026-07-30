---
id: EG-310
title: Migration Execution, Locks and Transactional Boundaries
summary: Defines provider-independent migration execution with explicit authorization, exclusive locking, capability-aware transactions, safe result reporting and atomic history coordination for WP-217.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-30
updated: 2026-07-30
work_package: WP-217
tags:
  - foundation
  - database
  - migrations
  - execution
  - transactions
depends_on:
  - EG-305
  - EG-306
  - EG-307
  - EG-308
  - EG-309
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-310 — Migration Execution, Locks and Transactional Boundaries

## 1. Purpose

This increment introduces the provider-independent execution coordinator for authorized migration plans. Concrete SQL, connection and persistence adapters remain outside the core executor.

## 2. Authorization and execution mode

`MigrationExecutor` SHALL reject dry-run plans and SHALL verify explicit authorization before acquiring resources or invoking handlers. Authorization mismatch SHALL fail before state mutation.

## 3. Exclusive execution lock

Execution SHALL acquire one exclusive lock using a safe owner token derived from the authorization identifier. Failure to acquire the lock SHALL stop execution before any handler invocation. An acquired lock SHALL be released through a `finally` boundary.

## 4. Operation handlers

Migration operations SHALL execute only through `MigrationOperationHandlerInterface`. Exactly one handler SHALL support each selected descriptor. Missing and ambiguous handlers SHALL fail through typed exceptions.

## 5. Transaction boundaries

Transaction behavior SHALL be capability-driven through `MigrationTransactionManagerInterface`. When transactions are supported, each migration and its history mutation SHALL share one transaction boundary. Failure SHALL trigger rollback and stop subsequent migrations. Providers without transactional DDL SHALL execute without synthetic transaction calls.

## 6. History coordination

Successful `up` operations SHALL append an `applied` history record. Successful `down` operations SHALL append a `rolled_back` record. History SHALL change only after the operation handler reports success.

## 7. Safe reporting

Execution reports SHALL preserve plan fingerprint, direction, contiguous sequence, identifiers and safe status codes. Raw SQL, checksums, credentials, connection details and exception messages SHALL NOT enter reports.

## 8. Failure policy

A handler-declared failure or exception SHALL stop the plan. Exceptions SHALL be normalized to a safe code. Rollback failure SHALL not prevent lock release. Previously committed migrations SHALL not be compensated automatically.

## 9. Acceptance criteria

The increment is accepted when authorized plans execute deterministically, locks are always released, transactions follow provider capability, history changes atomically with successful operations, failures stop subsequent work, and PHPUnit, PHPStan and SIF Builder remain green.
