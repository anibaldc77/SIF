---
id: EG-311
title: Reference Adapters and Installer Integration
summary: Defines deterministic in-memory migration reference adapters and governed Installer integration without coupling the migration core to database drivers or the Installer runtime.
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
  - adapters
  - installer
depends_on:
  - EG-305
  - EG-306
  - EG-307
  - EG-308
  - EG-309
  - EG-310
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-311 — Reference Adapters and Installer Integration

## Status
Approved implementation increment for WP-217-I7.

## Purpose
Provide deterministic in-memory reference adapters for migration history, locking and transactional boundaries, and bridge governed installer migration mutations to the migration executor without coupling the migration core to the installer runtime or a database driver.

## Requirements
- Reference adapters MUST implement the existing migration contracts.
- In-memory history MUST expose deterministic ordering and replacement by migration identifier.
- Lock ownership MUST be exclusive and release MUST be owner-safe.
- Transaction journaling MUST be observable for tests.
- Installer integration MUST support only migration-classified `execute-migrations` mutations.
- Plans and authorizations MUST be supplied through a dedicated contract.
- Failed migration reports MUST fail the installer mutation.
- Compensation MUST use an explicit rollback plan and authorization.
- No PDO, SQL dialect, filesystem or global runtime dependency is permitted.

## Security and governance
Execution remains authorization-bound. The bridge does not derive authorizations, serialize checksums, or expose internal exception messages in installer result metadata.
