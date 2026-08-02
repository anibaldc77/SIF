---
id: EG-351
title: Validation First-Run and Example Application
summary: Defines deterministic skeleton validation, fingerprint-bound first-run execution and a governed minimal example application.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-222
tags:
  - foundation
  - application-skeleton
  - first-run
  - validation
  - example-application
depends_on:
  - EG-345
  - EG-346
  - EG-347
  - EG-348
  - EG-349
  - EG-350
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Validation, First-Run and Example Application

WP-222 I7 defines explicit post-generation validation, a fingerprint-bound first-run coordinator and a minimal example application blueprint.

## Requirements

- First-run MUST be dry-run by default.
- Execution MUST require authorization bound to the current plan fingerprint.
- Generated artifacts MUST be validated by type and SHA-256 fingerprint.
- Validation MUST not expose secrets or execute application code.
- The example application MUST use the same governed template factories as normal projects.
- Repeating first-run against unchanged artifacts MUST remain idempotent.
