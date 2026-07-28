---
id: EG-285
title: Safe Failure Metadata, Context Enrichment and Envelope Factory
summary: Defines bounded normalization, secret redaction, execution-context enrichment and immutable failure-envelope creation.
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
  - metadata
  - redaction
  - execution-context
  - foundation
work_package: WP-214
depends_on:
  - EG-281
  - EG-282
  - EG-283
  - EG-284
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-285 — Safe Failure Metadata, Context Enrichment and Envelope Factory

## Status
Implemented by WP-214-I5.

## Decision
Failure metadata crosses a trust boundary before entering `FailureEnvelope`. The boundary is composed from explicit, provider-neutral contracts: enrichers add known context, redactors remove secret-bearing values, and a bounded normalizer converts arbitrary input into structured scalar data.

## Guarantees
- Depth, item count and string length are bounded.
- Unsupported values never leak object internals.
- Throwables are projected only as type, message and code.
- Secret keys are redacted recursively and case-insensitively.
- Execution context is projected through its public contract.
- Custom context attributes are opt-in.
- The factory preserves the original Throwable by identity.
- Identifier generation and time remain injectable boundaries.

## Non-goals
This increment does not classify throwables, decide recovery, report failures, schedule retries, log recursively or install runtime handlers.
