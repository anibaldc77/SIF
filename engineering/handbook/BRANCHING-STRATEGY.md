---
id: BRANCHING-STRATEGY
title: Branching Strategy
summary: main is protected, reviewable, and always integrable. Work branches use one of: feature/*, fix/*, docs/*, release/*, or hotfix/*.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-07-15
updated: 2026-07-22
tags:
  - branching
  - strategy
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# Branching Strategy

`main` is protected, reviewable, and always integrable. Work branches use one of: `feature/*`, `fix/*`, `docs/*`, `release/*`, or `hotfix/*`.

SIF does not use a `develop` branch. Introducing one requires an approved ADR documenting a concrete release-management need.
