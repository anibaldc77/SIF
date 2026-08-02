---
id: EG-349
title: CLI Integration and Application Create Command
summary: Defines CLI planning and authorized execution for deterministic application skeleton creation.
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
  - cli
  - application-creation
  - authorization
depends_on:
  - EG-345
  - EG-346
  - EG-347
  - EG-348
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# CLI Integration and Application Create Command

## Objective
Expose application skeleton creation through `app:create` while preserving dry-run, deterministic fingerprints, fail-closed authorization and the generation contracts established in WP-222 I1-I4.

## Rules
- Planning is the default behavior.
- `--execute` requests execution but is not itself authorization.
- Authorization must match the exact SHA-256 plan fingerprint.
- Plans containing conflicts are rejected before execution.
- The command does not create target directories, run Composer, create `.env`, execute Installer or run migrations.
- Manifest and filesystem construction are supplied explicitly by composition.

## Exit codes
- `0`: plan produced or authorized execution completed.
- `4`: generation plan contains conflicts.
- `5`: execution requested without matching authorization.
