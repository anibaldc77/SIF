---
id: WP-108-I5
title: Document Classification Normalization
summary: Records the canonical category and document_class normalization performed during WP-108 Increment 5.
status: Draft
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - builder
  - metadata
  - category
  - document-class
  - migration
work_package: WP-108
depends_on:
  - EG-047
  - EG-048
  - EG-049
  - ES-002
  - ES-003
related_adrs: []
supersedes: null
superseded_by: null
---

# WP-108 Increment 5 — Document Classification Normalization

## 1. Objective

Normalize existing governed artifacts to the canonical `category` and `document_class` enumerations defined by ES-002 and ES-003 without changing document bodies, identifiers, versions, lifecycle states or dependency relationships.

## 2. Classification rules

The migration applies these functional mappings:

| Artifact function | category | document_class |
|---|---|---|
| Work Package definition or package README | `Work Package` | `GovernanceDocument` |
| Technical engineering specification | `Normative Specification` | `NormativeDocument` |
| Implementation completion or end-to-end validation evidence | `Implementation Review` | `ReviewDocument` |
| Summary, index or navigation document | `Informative Document` | `InformativeDocument` |

Legacy keys such as `class: specification` are removed because ES-002 reserves `document_class` as the canonical classification field.

## 3. Scope

Twenty governed documents are normalized. The increment does not:

- modify Markdown bodies;
- change lifecycle status values;
- repair filename and identifier inconsistencies;
- complete missing `summary` values;
- repair reference integrity findings;
- generate governed output artifacts.

## 4. Validation result

Validation against the real Builder pipeline after applying Increment 4 and this migration produced:

- `DOCCONS-203`: 0 findings;
- `META_DOCUMENT_CLASS`: 0 findings;
- `META_CLASS_CATEGORY`: 0 findings.

Remaining diagnostics belong to later WP-108 increments, principally missing Front Matter, filename consistency, date correction, reference repair and generated artifacts.

## 5. Environment note

The package was structurally validated and executed through `php bin/sif-builder validate` in the available container. Full PHPUnit and PHPStan execution remains assigned to the Windows project environment because the container PHP runtime lacks the `dom`, `mbstring` and `xmlwriter` extensions and does not provide Composer.
