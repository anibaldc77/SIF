---
id: QUALITY-GATE
title: Quality Gate
summary: Every merge candidate must pass:.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-07-15
updated: 2026-07-22
tags:
  - quality
  - gate
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# Quality Gate

Every merge candidate must pass:

1. `composer validate --strict`
2. `composer test`
3. `composer analyse` at PHPStan level 8 with zero errors
4. `composer style:check` in dry-run mode
5. Valid `component.json` and `component.lock`
6. Updated documentation and Work Package implementation report
7. A clean Git worktree after validation

Failures are fixed in the Work Package branch; they are never waived by changing production behavior without an approved specification or ADR.
