---
id: EG-350
title: Module Migration and Model Templates
summary: Defines deterministic user-owned templates for modules, BaseModel 2.0 models and migration source files.
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
  - templates
  - modules
  - migrations
  - models
depends_on:
  - EG-345
  - EG-346
  - EG-347
  - EG-348
  - EG-349
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Module Migration and Model Templates

## Objective
Define deterministic source templates for application modules, BaseModel 2.0 models and migration files without overwriting application-owned code.

## Rules
- Generated application code is `user-owned`.
- Generated application code uses overwrite policy `fail`.
- Namespaces and names are validated before rendering.
- Template output uses UTF-8 compatible LF line endings.
- Model templates support simple or composite identities, timestamps and optional soft delete declarations.
- Migration templates are inert source files and are never executed during generation.
- Module templates do not register themselves through reflection or filesystem scanning.
