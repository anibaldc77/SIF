---
id: EG-049
title: Front Matter Migration
summary: Defines the controlled migration of SIF-owned Markdown documents to the canonical YAML Front Matter schema without fabricating historical metadata.
status: Draft
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-22
updated: 2026-07-22
tags:
  - builder
  - metadata
  - migration
  - yaml
work_package: WP-108
depends_on:
  - EG-047
  - EG-048
  - ES-002
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-049 — Front Matter Migration

## 1. Purpose

This increment defines a controlled migration for SIF-owned Markdown documents that currently lack YAML Front Matter or contain incomplete Front Matter.

The migration SHALL reduce repository diagnostics by making documents conform to ES-002 while preserving documentary history and avoiding invented facts.

## 2. Scope

This increment covers:

- classification of discovered SIF-owned Markdown;
- detection of missing and incomplete Front Matter;
- canonical metadata templates;
- manual completion rules for historical fields;
- deterministic audit output;
- staged integration and verification.

## 3. Out of Scope

This increment does not:

- alter Builder analyzers or diagnostic severity;
- infer authors from Git commits;
- rewrite document bodies;
- rename identifiers or files;
- resolve broken references;
- normalize every category or document class;
- generate governed artifacts.

Category and document-class normalization continue in later WP-108 increments.

## 4. Migration Principles

### FM-001 — Preserve history

Existing valid metadata values SHALL be preserved unless objectively invalid under ES-002.

### FM-002 — No fabricated facts

Authors, creation dates, approval status, dependencies and ADR relationships SHALL NOT be invented.

When a value cannot be established from repository evidence, the document SHALL be placed in the manual-review queue.

### FM-003 — Additive migration

The migration SHALL add or complete Front Matter without modifying the substantive Markdown body.

### FM-004 — Deterministic formatting

Canonical key order SHALL follow ES-002:

```text
id
title
summary
status
version
category
document_class
authors
created
updated
tags
work_package
depends_on
related_adrs
supersedes
superseded_by
```

### FM-005 — UTF-8 and first content

The opening `---` delimiter SHALL be the first content in the file. Files SHALL remain UTF-8.

### FM-006 — Review before write

Automated tooling in this increment SHALL audit and propose classifications only. It SHALL NOT rewrite repository files.

## 5. Migration Classes

Each governed Markdown file SHALL be assigned one migration class:

| Class | Condition | Action |
|---|---|---|
| `compliant` | Complete and valid Front Matter | No change |
| `missing_front_matter` | No opening YAML block | Add reviewed template |
| `incomplete_front_matter` | YAML exists but required fields are absent or empty | Complete reviewed fields |
| `invalid_front_matter` | Delimiters or YAML shape are invalid | Repair structure manually |
| `manual_review` | Required historical values cannot be established | Resolve before migration |
| `excluded` | Not SIF-owned or outside discovery policy | No migration |

## 6. Evidence Sources

Permitted evidence for metadata completion, in descending order of authority:

1. existing Front Matter in the same document;
2. approved specification or standard that names the document;
3. repository history and original Work Package delivery;
4. document heading and body;
5. file path and naming convention.

A lower-ranked source SHALL NOT override a higher-ranked source.

## 7. Field Rules

### 7.1 Identifier

The existing canonical identifier SHALL be retained. When no identifier exists, migration SHALL derive a candidate only from an explicit identifier in the title or governing package. Filename-only derivation requires review.

### 7.2 Title and summary

The primary heading MAY supply `title`. `summary` SHALL be a concise description supported by the document body.

### 7.3 Status

A missing status SHALL default only to `Draft` for newly introduced WP-108 migration documents. Historical documents require evidence; they SHALL NOT be promoted to `Approved` merely because they exist in the repository.

### 7.4 Dates

`created` and `updated` require documentary or repository-history evidence. Filesystem timestamps are advisory and SHALL NOT be treated as authoritative when copied or extracted archives may have altered them.

### 7.5 Authors

Use the author or governing body explicitly recorded by the source package. Do not assign `SIF Architecture Board` to historical documents without evidence.

### 7.6 Relationships

`depends_on` and `related_adrs` SHALL be empty arrays when the document explicitly has no known relationships. They SHALL NOT be populated from speculative textual similarity.

## 8. Batch Strategy

Migration SHALL proceed in small batches:

1. current WP-108 documents;
2. active standards and models;
3. WP-100 through WP-107 specifications;
4. implementation and architecture reviews;
5. root and handbook documents;
6. remaining SIF-owned Markdown.

Each batch SHALL be committed independently and validated before the next batch begins.

## 9. Audit Tool

`tools/builder/scripts/audit-front-matter.ps1` SHALL:

- enumerate Markdown below a selected repository root;
- honor the WP-108 default excluded segments;
- classify files without writing them;
- emit deterministic console output;
- optionally write a CSV report;
- return exit code `0` after a successful audit regardless of findings;
- return a non-zero code only for execution failures.

The Builder remains the authoritative validator. The script is a migration aid, not a replacement analyzer.

## 10. Verification

For every batch:

```powershell
php bin/sif-builder validate
vendor/bin/phpunit
vendor/bin/phpstan analyse
git diff --check
```

The diagnostic count SHALL be recorded after each batch. Existing baseline measurements SHALL never be overwritten.

## 11. Acceptance Criteria

This increment is accepted when:

- the audit script runs deterministically on Windows PowerShell;
- no repository file is changed by the audit;
- all WP-108 migration templates conform to ES-002;
- the first migration batch is identified;
- full PHPUnit and PHPStan validation remain green.
