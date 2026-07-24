---
id: EG-052
title: Repository Metadata Migration
summary: Defines the systematic migration of governed repository documentation to canonical YAML Front Matter.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - metadata
  - migration
  - documentation
work_package: WP-111
depends_on:
  - EG-051
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-052 — Repository Metadata Migration

## Objective

Migrate every governed Markdown document remaining in the repository to canonical YAML Front Matter without modifying its technical body.

## Rules

- Preserve existing technical content.
- Use canonical lifecycle, category, and document class values.
- Derive creation dates from Git history when available.
- Add the recommended `summary` field to governed documents.
- Keep generated artifacts and excluded dependency trees outside discovery.

## Acceptance criteria

- No `REPOSITORY-101` diagnostics caused by missing or invalid Front Matter.
- No metadata format errors.
- Existing Builder behavior remains unchanged.
