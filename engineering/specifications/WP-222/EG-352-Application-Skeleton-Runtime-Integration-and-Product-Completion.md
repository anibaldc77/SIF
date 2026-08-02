---
id: EG-352
title: Application Skeleton Runtime Integration and Product Completion
summary: Defines optional runtime composition, application publication, capabilities and final product boundaries for the governed application skeleton.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-222
tags:
  - foundation
  - application-skeleton
  - runtime
  - service-provider
  - product-completion
depends_on:
  - EG-345
  - EG-346
  - EG-347
  - EG-348
  - EG-349
  - EG-350
  - EG-351
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Application Skeleton Runtime Integration and Product Completion

WP-222 I8 defines the optional runtime boundary that publishes application-skeleton composition services without generating files or executing first-run during bootstrap.

## Requirements

- Application Skeleton Runtime MUST remain optional.
- Runtime construction MUST NOT inspect or mutate a filesystem.
- Filesystem-dependent validation MUST be created explicitly for a supplied filesystem adapter.
- The Service Provider MUST publish the runtime only to compatible mutable applications.
- Capabilities MUST identify the skeleton and first-run facilities without implying execution.
- Bootstrap MUST NOT execute generation, Composer, Installer, migrations or application code.
- Runtime summaries MUST expose composition state only and MUST NOT expose paths, file contents, credentials or environment values.
- CLI command registration and authorization MUST remain explicit composition concerns.

## Product boundary

The completed subsystem provides immutable project manifests, deterministic blueprints, generation plans, strict templates, CLI orchestration, code templates, first-run authorization, validation, example composition and runtime publication. External process execution and deployment remain outside WP-222.
