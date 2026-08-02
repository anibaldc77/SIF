---
id: EG-342
title: Migration and Installer Commands
summary: Defines planning, validation and explicitly authorized mutation commands for SIF migrations and installation operations.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-221
tags:
  - foundation
  - cli
  - migration
  - installer
depends_on:
  - EG-337
  - EG-338
  - EG-339
  - EG-340
  - EG-341
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-342 — Migration and Installer Commands

## 1. Purpose

I6 exposes Migration and Installer operations through explicit command adapters. Commands SHALL reuse existing planning, dry-run, integrity, authorization, lock, transaction, journaling and compensation semantics.

## 2. Migration commands

The CLI SHALL provide `migration:status`, `migration:plan`, `migration:run` and `migration:rollback`. Status and planning are non-mutating. Run and rollback SHALL require an authorization matching the exact plan fingerprint, direction and execution mode.

## 3. Installer commands

The CLI SHALL provide `installer:assess`, `installer:plan` and `installer:run`. Assessment and planning SHALL remain non-mutating. Execution SHALL require an executable dry-run report and explicit authorization matching its installation identifier and mutation-plan fingerprint.

## 4. Exit codes

Authorization absence or rejection SHALL use exit code 5. Unsatisfied requirements SHALL use exit code 6. Incomplete or compensated execution SHALL use exit code 7. Validation and integrity failures SHALL preserve governed validation semantics.

## 5. Composition boundary

Request construction, mutation-plan supply and authorization issuance SHALL be injected through explicit operation contexts. Commands SHALL NOT manufacture authorization from a confirmation prompt or read secrets from process globals.

## 6. Scope boundary

I6 does not add module, resource or maintenance commands and does not register commands automatically in the application runtime.
