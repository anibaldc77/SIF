---
id: EG-309
title: Migration Selection, Dry-Run and Execution Authorization
summary: Defines deterministic request-aware migration selection, safe dry-run reporting and explicit authorization bound to the exact execution plan for WP-217.
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
  - planning
  - authorization
depends_on:
  - EG-305
  - EG-306
  - EG-307
  - EG-308
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-309 — Migration Selection, Dry-Run and Execution Authorization

## 1. Purpose

This increment converts a validated registry and history snapshot into an immutable request-aware execution plan. It provides safe inspection and requires explicit authorization before any future state-changing executor may act.

## 2. Selection

`MigrationSelector` SHALL assert history integrity before selection. Forward selection SHALL include migrations that are absent or rolled back. Reverse selection SHALL include currently applied migrations and preserve reverse topological order.

Target, tag and limit constraints SHALL never reorder migrations. An unknown target SHALL fail with a typed exception. A selected reverse migration SHALL be explicitly reversible.

## 3. Execution plan

`MigrationExecutionPlan` SHALL bind the complete canonical request to the selected `MigrationPlan`. Its SHA-256 fingerprint SHALL change when direction, execution mode, target, tags, limit, descriptor content or selected order changes.

## 4. Dry-run

`MigrationDryRunReport` SHALL expose direction, mode, count, fingerprint and ordered migration identifiers. It SHALL NOT expose checksums, SQL, credentials, connection details or exception messages.

## 5. Authorization

`MigrationExecutionAuthorization` SHALL contain a safe authorization identifier, the exact plan fingerprint, direction, mode and an explicit execution permission. `MigrationExecutionAuthorizer` SHALL reject review-only authorization and any mismatch in fingerprint, direction or mode.

Authorization SHALL NOT be inferred from dry-run completion, environment state or request ownership.

## 6. Safety boundary

This increment SHALL NOT open connections, execute SQL, acquire locks, begin transactions or mutate migration history. Execution remains deferred to I6.

## 7. Acceptance criteria

The increment is accepted when selection is deterministic and history-aware, reverse selection rejects irreversible migrations, dry-run output is safe, authorization is exact and explicit, and PHPUnit, PHPStan and SIF Builder remain green.
