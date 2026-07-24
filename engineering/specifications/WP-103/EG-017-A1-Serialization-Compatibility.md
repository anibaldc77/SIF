---
id: EG-017-A1
title: BuilderResult Serialization Compatibility
summary: BuilderResult::jsonSerialize() retains the historical top-level key diagnostic_counts while also exposing the richer statistics structure introduced by EG-017.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-21
updated: 2026-07-22
tags:
  - builderresult
  - serialization
  - compatibility
work_package: WP-103
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-017-A1 — BuilderResult Serialization Compatibility

## Decision

`BuilderResult::jsonSerialize()` retains the historical top-level key
`diagnostic_counts` while also exposing the richer `statistics` structure
introduced by EG-017.

## Rationale

`BuilderResult` is a public serializable value object. Removing an existing key
constitutes an avoidable backward-incompatible change. The compatibility key is
derived from `ExecutionStatistics::diagnosticsBySeverity`, so both projections
remain consistent and deterministic.

## Resulting structure

- `diagnostic_counts`: backward-compatible severity map.
- `statistics.diagnostics_by_severity`: canonical reporting statistics map.

No deprecation is introduced in this amendment.
