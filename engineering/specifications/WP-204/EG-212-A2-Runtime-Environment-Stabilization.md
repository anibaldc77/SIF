---
id: EG-212-A2
title: Runtime Environment Stabilization
summary: Restores capability compatibility, static contract precision, and governed metadata after WP-204-I3A validation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - runtime
  - environment
  - compatibility
  - stabilization
work_package: WP-204
depends_on:
  - EG-212
  - EG-212-A1
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-212-A2 — Runtime Environment Stabilization

## Decision

The environment repository is part of the runtime graph but SHALL NOT be added to the historical default capability list in WP-204. Capability discovery remains unchanged until a separately governed capability migration is approved.

Tests that use `Framework::create()` SHALL narrow the returned base contract explicitly before invoking environment-aware methods.

`EnvironmentRepository` SHALL declare the complete generic array type accepted by its constructor.

Corrective documents SHALL use registered metadata categories and document classes and SHALL declare a Semantic Versioning-compatible `version` field.

## Acceptance criteria

- Existing capability tests retain the WP-203 deterministic list.
- Environment repository access remains available through `EnvironmentAwareApplicationInterface`.
- PHPStan reports zero errors.
- Builder reports zero diagnostics.
- Governed generation is deterministic.
