---
id: RELEASE-PROCESS
title: Release Process
summary: 1. Confirm the Quality Gate on the release branch. 2. Update VERSION, CHANGELOG.md, component metadata, and implementation reports. 3. Review public API compatibility and approved ADRs. 4. Tag the reviewed commit and publish release notes.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-07-15
updated: 2026-07-22
tags:
  - release
  - process
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# Release Process

1. Confirm the Quality Gate on the release branch.
2. Update `VERSION`, `CHANGELOG.md`, component metadata, and implementation reports.
3. Review public API compatibility and approved ADRs.
4. Tag the reviewed commit and publish release notes.

No release may contain ignored build artifacts, credentials, or unreviewed dependency changes.
