---
id: EG-282
title: Core Failure Model
summary: Defines the immutable provider-neutral failure vocabulary for Error Handling and Recovery 2.0.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-28
updated: 2026-07-28
tags:
  - error-handling
  - recovery
  - failure-model
  - foundation
work_package: WP-214
depends_on:
  - EG-281
related_adrs: []
supersedes: null
superseded_by: null
---

# Core Failure Model

## Purpose

Define the immutable, provider-neutral vocabulary used by Error Handling and Recovery 2.0.

## Model

A `FailureEnvelope` preserves the original `Throwable` and associates it with a stable identifier, UTC timestamp, category, severity, disposition, origin and bounded structured metadata.

## Invariants

- The original throwable is never replaced.
- Summary projection exposes only throwable type, message and code.
- Stack traces and arbitrary object state are excluded.
- Metadata accepts only recursively structured scalar values.
- Clocks are injected through `FailureClockInterface`.
- Value objects are immutable and validate their canonical form at construction.

## Canonical vocabularies

Categories: application, configuration, dependency, infrastructure, security, validation, unknown.

Severity: debug, info, warning, error, critical.

Disposition: transient, permanent, invalid, unknown.

## Deferred concerns

Classification rules, recovery decisions, retry guidance, context enrichment, redaction, reporters and runtime integration are intentionally deferred to later increments.
