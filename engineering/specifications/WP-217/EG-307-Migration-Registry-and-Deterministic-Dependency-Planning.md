---
id: EG-307
title: Migration Registry and Deterministic Dependency Planning
summary: Defines the explicit migration registry, duplicate detection, dependency graph validation, deterministic forward ordering, rollback ordering and immutable plan fingerprinting for WP-217.
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
depends_on:
  - EG-305
  - EG-306
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-307 — Migration Registry and Deterministic Dependency Planning

## 1. Purpose

This increment establishes the read-only registry and dependency planning boundary of the migration engine.

## 2. Registry

`MigrationRegistry` SHALL accept only `MigrationDescriptor` values, reject duplicate case-sensitive identifiers and expose stable lookup without filesystem, database, Composer or module discovery concerns.

Registration order SHALL NOT affect the resulting execution plan.

## 3. Graph validation

Every declared dependency SHALL exist in the registry. Self-dependencies remain prohibited by the descriptor model. The planner SHALL detect dependency cycles before producing a plan and SHALL return typed migration exceptions for missing dependencies and cycles.

## 4. Deterministic ordering

Forward plans SHALL use topological ordering. Simultaneously ready migrations SHALL be ordered by version and then by case-sensitive migration identifier. Versioned migrations precede unversioned migrations.

Rollback plans SHALL be the exact reverse of the validated forward topological order. Selection against history and reversibility enforcement are deferred to later increments.

## 5. Immutable plans

`MigrationPlan` SHALL preserve direction, ordered descriptors and a deterministic SHA-256 fingerprint derived only from canonical safe descriptor summaries and direction. It SHALL reject untyped or duplicate members.

## 6. Safety boundaries

This increment SHALL NOT access database connections, history stores, locks, transactions, SQL, filesystems or runtime services. Planning remains pure and read-only.

## 7. Acceptance criteria

The increment is accepted when duplicate identifiers, missing dependencies and cycles are rejected; forward and rollback orders are deterministic; fingerprints are stable across registration order; PHPUnit, PHPStan and SIF Builder remain green.
