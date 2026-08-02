---
id: WP-222-I1-ARCHITECTURE-REVIEW
title: WP-222 I1 Architecture Review
summary: Reviews the canonical SIF application structure, project manifest boundary, path ownership, deterministic scaffolding, overwrite protection, secret handling, cross-platform requirements and idempotent first-run model.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-02
updated: 2026-08-02
work_package: WP-222
tags:
  - application
  - skeleton
  - scaffolding
  - first-run
  - cli
  - installer
  - architecture
  - review
depends_on:
  - EG-345
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-222 I1 Architecture Review

## Scope reviewed

- separation between the SIF framework and generated applications;
- canonical application directory structure;
- project manifest responsibilities;
- skeleton-owned, user-owned and runtime-owned paths;
- deterministic generation and mutation planning;
- overwrite and upgrade policies;
- application and CLI bootstrap boundaries;
- environment configuration and secret handling;
- explicit, idempotent first-run lifecycle;
- integration with Installer and Developer CLI;
- template safety;
- Windows and Unix portability;
- application validation and minimal example requirements;
- eight-increment delivery sequence.

## Architectural decision

WP-222 SHALL introduce an application skeleton as a consumer of public SIF contracts.

The framework SHALL NOT copy its Foundation source tree into generated projects and SHALL NOT depend on generated application classes.

The required direction is:

```text
Generated application -> SIF public contracts -> selected runtime adapters
```

Application creation SHALL be planned before mutation and SHALL reject unauthorized or conflicting targets by default.

## Ownership decision

Every generated path SHALL be classified as:

```text
skeleton-owned
user-owned
runtime-owned
```

User-owned files SHALL never be overwritten silently. Runtime-owned paths SHALL be excluded from version control by default. Skeleton-owned upgrades SHALL require an explicit, reviewable plan.

## Bootstrap decision

Application bootstrap and first-run SHALL remain separate.

Bootstrap may compose runtimes and providers, but SHALL NOT execute migrations, Installer mutations, resource publication or cache cleanup.

First-run SHALL be an explicit, authorization-aware workflow capable of dry-run and safe repetition.

## Manifest decision

Every application SHALL contain `sif.project.json` with portable, non-secret project metadata and skeleton compatibility information.

The manifest SHALL not contain credentials, tokens, private keys or machine-specific temporary state.

## Cross-platform decision

Logical paths in manifests and templates SHALL use portable `/` separators. Physical paths SHALL be resolved through dedicated path logic.

Generated text SHALL use UTF-8 without BOM, and launcher behavior SHALL be available for both Windows and Unix-like environments.

## Readiness decision

WP-222 is correctly sequenced after WP-221.

The repository already contains the required foundations:

- Installer planning, authorization and rollback;
- Migration Engine and PDO adapters;
- Configuration and Runtime composition;
- Modules and Resources;
- BaseModel 2.0;
- Developer CLI and entry points.

I2 may proceed with the immutable project manifest and skeleton value model without introducing filesystem mutations.

## Risks retained for later increments

The following remain intentionally unresolved in I1:

- exact JSON schema for `sif.project.json`;
- concrete path and namespace value objects;
- template renderer implementation;
- filesystem mutation journal;
- final `app:create` syntax;
- first-run persistence marker format;
- skeleton upgrade diff format;
- example application source.

These concerns are assigned to I2–I8 and SHALL not be implemented ad hoc before their governed increment.

## Approval recommendation

Approve I1 and proceed to:

```text
WP-222 I2 — Immutable Project Manifest and Skeleton Value Model
```
