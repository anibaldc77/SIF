---
id: EG-308
title: Migration History and Integrity
summary: Defines immutable migration history records, storage contracts, deterministic history snapshots and integrity verification against the registered migration catalog for WP-217.
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
  - history
  - integrity
depends_on:
  - EG-305
  - EG-306
  - EG-307
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-308 — Migration History and Integrity

## 1. Purpose

This increment establishes the provider-independent history and integrity boundary of the migration engine. It represents migration state without opening database connections or defining a physical history table.

## 2. History records

`MigrationHistoryRecord` SHALL be immutable and preserve the migration identifier, checksum, lifecycle status, timestamp, optional version and optional safe batch token. Its public summary SHALL contain canonical scalar values only.

The record SHALL NOT contain SQL, connection credentials, exception messages or arbitrary environment data.

## 3. History snapshots

`MigrationHistory` SHALL represent one deterministic current record per case-sensitive migration identifier. It SHALL reject untyped and duplicate members, provide stable lookup and expose identifiers in lexical order independent of provider return order.

An applied record represents currently installed state. A rolled-back record remains observable but is considered pending for forward planning.

## 4. Storage contract

`MigrationHistoryStoreInterface` SHALL isolate persistence behind operations for reading the current snapshot, locating a record, appending a record and removing the current record. Concrete database and in-memory adapters are deferred to later increments.

## 5. Integrity verification

`MigrationIntegrityChecker` SHALL compare the registered migration catalog against history and classify:

- historical identifiers missing from the registry;
- checksum mismatches for identifiers present in both sources;
- registered migrations that have not been applied or were rolled back.

Missing registry entries and checksum mismatches invalidate integrity. Pending migrations are expected operational state and do not invalidate integrity.

Integrity failures SHALL use typed exceptions and aggregate counts without exposing checksum values.

## 6. Safety boundaries

This increment SHALL NOT execute SQL, mutate history, acquire locks, start transactions, inspect schemas or select migrations for execution. It remains pure and deterministic.

## 7. Acceptance criteria

The increment is accepted when history records and snapshots are immutable and validated, provider ordering does not affect results, missing and modified migrations are detected, rolled-back migrations are pending, typed integrity failures are emitted and PHPUnit, PHPStan and SIF Builder remain green.
