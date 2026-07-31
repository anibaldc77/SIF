---
id: EG-320
title: PDO Migration Runtime Integration and Product Completion
summary: Defines explicit runtime publication of the completed PDO migration adapter composition and the governed completion boundary for WP-218.
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
  - database
  - migrations
  - pdo
  - runtime
  - completion
depends_on:
  - EG-313
  - EG-314
  - EG-315
  - EG-316
  - EG-317
  - EG-318
  - EG-319
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# PDO Migration Runtime Integration and Product Completion

## Purpose

Define the explicit runtime integration boundary that exposes the completed PDO migration adapter composition to SIF without performing database work during provider registration.

## Normative requirements

1. Runtime integration MUST receive an already composed `PdoMigrationAdapterComposition`.
2. Construction and provider registration MUST NOT create history tables, acquire locks, begin transactions, or execute migrations.
3. The provider MUST publish `migration` and `migration.pdo` capabilities.
4. The provider MUST install the composed `MigrationRuntime` only when the application implements `MutableMigrationApplicationInterface`.
5. Installer mutation handlers MUST be exposed explicitly. The history provisioning handler MUST always be present; the migration mutation handler MUST be present only when an installation plan provider was supplied during composition.
6. Runtime activation MUST remain opt-in through normal SIF service-provider registration or direct bootstrap injection of the composed runtime.
7. Database credentials, DSNs, SQL parameter values, and PDO internals MUST NOT appear in summaries or capability metadata.

## Completion boundary

WP-218 completes the concrete PDO adapter layer for PostgreSQL, MySQL, and SQL Server. Automatic migration execution during application boot remains prohibited.
