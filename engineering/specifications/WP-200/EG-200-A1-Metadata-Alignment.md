---
id: EG-200-A1
title: WP-200 Architecture Decision Metadata Alignment
summary: Aligns ADR-0005 with the registered SIF metadata vocabulary and records the regression requirement.
status: Approved
version: 1.0.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-23
updated: 2026-07-23
tags:
  - runtime
  - metadata
  - adr
  - corrective-action
work_package: WP-200
depends_on:
  - EG-200
  - EG-201
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-200-A1 — WP-200 Architecture Decision Metadata Alignment

## Purpose

Correct the metadata declaration of ADR-0005 without changing the approved capability-driven Runtime decision.

## Root cause

ADR-0005 declared values outside the vocabulary enforced by `CoreMetadataValidator`:

- `status: Accepted`, while the registered stable state is `Approved`;
- `document_class: DecisionDocument`, while `Architecture Decision Record` belongs to `GovernanceDocument`.

Because invalid documents are excluded from the metadata registry, the ADR could not be resolved by EG-200, EG-201, or the WP-200 architecture review. The resulting `REFERENCE-404` and `REFINT-201` diagnostics were secondary effects.

## Required correction

ADR-0005 SHALL declare:

```yaml
status: Approved
category: Architecture Decision Record
document_class: GovernanceDocument
```

No new document class, status value, registry, or compatibility alias SHALL be introduced for this correction.

## Regression requirement

`CoreMetadataValidatorTest` SHALL include a valid Architecture Decision Record case using the registered governance taxonomy.

## Non-goals

This corrective action does not:

- modify the capability-driven Runtime architecture;
- redesign the metadata subsystem;
- introduce a second metadata registry;
- broaden the accepted vocabulary;
- change reference resolution behavior.

## Acceptance criteria

1. ADR-0005 is registered under identifier `ADR-0005`.
2. References from EG-200, EG-201, and WP-200-ARCHITECTURE-REVIEW resolve.
3. `META_DOCUMENT_CLASS` and `META_ENUM` are absent.
4. `REFERENCE-404` and `REFINT-201` caused by ADR-0005 are absent.
5. PHPUnit and PHPStan complete without regressions.
6. Final Builder validation reports zero diagnostics after governed artifacts are regenerated when required.
