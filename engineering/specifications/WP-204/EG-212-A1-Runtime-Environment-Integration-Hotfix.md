---
id: EG-212-A1
title: Runtime Environment Integration Hotfix
summary: Restores CLI compatibility, contract visibility, and approved runtime shutdown transitions for WP-204-I3.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
work_package: WP-204
tags:
  - runtime
  - environment
  - compatibility
  - hotfix
depends_on:
  - EG-212
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-212-A1 — Runtime Environment Integration Hotfix

## Decision

Native PHP sources are heterogeneous runtime maps, not pure environment-variable maps. `NativeEnvironmentProvider` MUST ignore null and non-scalar entries before delegating scalar normalization to `ArrayEnvironmentProvider`.

`ArrayEnvironmentProvider` remains strict for explicit caller-controlled arrays.

`Bootstrap::createApplication()` MUST expose its concrete environment-aware return contract covariantly so static analysis can resolve `variables()` without widening the historical base application contract.

The approved direct runtime shutdown transition from `booted` to `stopping` MUST remain valid.

## Acceptance criteria

- CLI metadata such as `$_SERVER['argv']` does not abort application creation.
- Explicit array providers still reject non-scalar values.
- Environment integration tests are statically typed without suppressions.
- `booted → stopping` is accepted.
- PHPUnit, PHPStan, Builder and deterministic generation pass.
