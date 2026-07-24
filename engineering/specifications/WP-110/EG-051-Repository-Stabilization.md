---
id: EG-051
title: Repository Stabilization
summary: Defines the corrective stabilization increment for scanner tests, lifecycle metadata, reference targets and template date validation.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-110
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - builder
  - stabilization
  - metadata
depends_on:
  - EG-050
related_adrs: []
references:
  - EG-050
---

# EG-051 — Repository Stabilization

## 1. Purpose

Stabilize the repository after the discovery-boundary correction delivered by WP-109, without changing the validated production behavior of `MarkdownRepositoryScanner`.

## 2. Scope

This increment:

1. corrects scanner tests so assertions count unique source documents rather than individual metadata diagnostics;
2. normalizes the lifecycle status of EG-026;
3. makes EG-010 and EG-032 discoverable as real reference targets;
4. migrates the seven existing documents referenced by EG-026 and EG-032 so the reference graph remains closed;
5. replaces invalid placeholder dates in the governed engineering-standard template with syntactically valid sample dates.

Bulk migration of the remaining metadata-less documents is explicitly deferred.

## 3. Test correction

A single invalid Markdown document may produce several `MetadataScanIssue` objects because `CoreMetadataValidator` reports each violated rule independently. Tests therefore SHALL assert the ordered list of unique issue paths when their intent is to verify candidate inclusion and ordering.

The scanner implementation SHALL remain unchanged by this correction.

## 4. Reference stabilization

References SHALL be resolved by registering their existing target documents, not by deleting technically valid relationships. Target documents migrated in this increment receive canonical Front Matter while their technical body remains unchanged.

## 5. Acceptance criteria

- the scanner test class passes in the project test environment;
- the complete PHPUnit suite passes;
- PHPStan level 8 reports no errors;
- `META_ENUM` is absent for EG-026;
- `DOCCONS-205` is absent for `engineering/standards/TEMPLATE.md`;
- `REFERENCE-404` and `REFINT-201` are absent for EG-026, EG-032, EG-034 and EG-035;
- no diagnostics originate from a `vendor` directory.
