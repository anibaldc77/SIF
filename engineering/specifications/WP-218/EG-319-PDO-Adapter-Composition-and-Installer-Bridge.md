---
id: EG-319
title: PDO Adapter Composition and Installer Bridge
summary: Defines explicit platform-aware composition of PDO migration adapters and governed Installer provisioning for the migration history table.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-31
updated: 2026-07-31
work_package: WP-218
tags:
  - foundation
  - migrations
  - pdo
  - installer
  - composition
depends_on:
  - EG-313
  - EG-314
  - EG-315
  - EG-316
  - EG-317
  - EG-318
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-319 — PDO Adapter Composition and Installer Bridge

## 1. Purpose

I7 composes the PDO connection, capability profile, persistent history store, lock, transaction manager and SQL operation handler into the existing WP-217 executor and runtime without changing WP-217 contracts.

## 2. Composition rules

Composition SHALL be explicit and side-effect free. Factory construction SHALL NOT initialize the history table, acquire a lock, begin a transaction or execute a migration. The default history policy SHALL therefore disable automatic initialization.

The composition SHALL expose the created adapters, executor, migration runtime and Installer handlers as one immutable object. A migration execution mutation handler SHALL be created only when a governed `MigrationInstallationPlanProviderInterface` is supplied.

## 3. History provisioning

History-table provisioning SHALL be represented by an Installer mutation with classification `migration` and operation `provision-migration-history`. Applying the mutation SHALL call explicit history initialization and return a deterministic receipt fingerprint derived from the logical table name.

Compensation SHALL NOT drop the history table. It SHALL return `compensation-unsupported`, preserving migration evidence and preventing destructive implicit rollback.

## 4. Security and compatibility

The bridge SHALL not contain DSNs, credentials, SQL parameter values or implicit authorization. Existing WP-217 in-memory adapters remain valid. Runtime service-provider registration remains reserved for I8.
