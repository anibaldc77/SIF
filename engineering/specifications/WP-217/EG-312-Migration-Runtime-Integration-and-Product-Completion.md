---
id: EG-312
title: Migration Runtime Integration and Product Completion
summary: Defines optional Foundation runtime integration, capability publication and completion boundaries for the governed WP-217 migration engine.
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
  - runtime
  - completion
depends_on:
  - EG-305
  - EG-306
  - EG-307
  - EG-308
  - EG-309
  - EG-310
  - EG-311
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-312 — Migration Runtime Integration and Product Completion

## Status
Approved implementation increment for WP-217 I8.

## Purpose
Publish the governed migration engine through the Foundation runtime without coupling the application kernel to database drivers or concrete SQL dialects.

## Requirements
- Application exposes an optional migration runtime.
- Bootstrap remains backward compatible when no migration runtime is configured.
- A service provider publishes the `migration` capability only during lifecycle registration.
- Runtime orchestration exposes integrity inspection, deterministic planning, dry-run reporting, and authorized execution.
- Registry, history store, selector, executor, locks, transactions, and operation handlers remain replaceable through existing contracts.
- Runtime integration must not create implicit execution authorizations or infer database credentials.

## Completion boundary
WP-217 delivers the framework-neutral migration engine. Concrete PDO history stores, database advisory locks, and SQL dialect handlers are separate adapter work packages.
