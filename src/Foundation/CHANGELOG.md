---
id: FOUNDATION-CHANGELOG
title: Changelog
summary: Documents changelog within the SIF engineering repository.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-07-15
updated: 2026-07-22
tags:
  - changelog
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# Changelog

## 1.0.0 — 2026-07-15

- Added Phase 4 immutable runtime event DTOs and safe JSON serialization.
- Added application capability discovery, normalization, validation, and deterministic registration.
- Added observability and capability tests without introducing automatic dispatch.
- Added Phase 3 Service Provider infrastructure.
- Added the provider contract, base class, ordered collection, and collection exceptions.
- Integrated provider registration, boot, and reverse shutdown with Application and Lifecycle.
- Added typed failure reporting for register, boot, and shutdown hooks.
- Added unit and integration coverage for provider ordering, lookup, duplication, and failures.
