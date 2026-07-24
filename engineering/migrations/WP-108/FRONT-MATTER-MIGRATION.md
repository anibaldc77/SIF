---
id: WP-108-FRONT-MATTER-MIGRATION
title: WP-108 Front Matter Migration Playbook
summary: Operational playbook for auditing, reviewing and migrating SIF-owned Markdown metadata in controlled batches.
status: Draft
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-22
updated: 2026-07-22
tags:
  - migration
  - metadata
  - front-matter
work_package: WP-108
depends_on:
  - EG-049
  - ES-002
related_adrs: []
supersedes: null
superseded_by: null
---

# WP-108 Front Matter Migration Playbook

## Audit

Run from the repository root:

```powershell
powershell -ExecutionPolicy Bypass -File tools\builder\scripts\audit-front-matter.ps1
```

To produce a CSV inventory:

```powershell
powershell -ExecutionPolicy Bypass -File tools\builder\scripts\audit-front-matter.ps1 `
  -RepositoryRoot D:\SIF `
  -CsvPath engineering\migrations\WP-108\front-matter-audit.csv
```

The script is read-only. Review the report before editing files.

## Review order

Prioritize:

1. `engineering/specifications/WP-108/`;
2. `engineering/standards/`;
3. `engineering/models/`;
4. active Work Packages;
5. reviews and handbooks;
6. root-level project documents.

## Per-file checklist

- confirm that the file is SIF-owned;
- identify authoritative `id` and title;
- preserve valid existing metadata;
- establish author and dates from evidence;
- use registered status, category and document class;
- use arrays for `authors`, `tags`, `depends_on` and `related_adrs`;
- keep `null` as YAML null, not an empty string;
- do not change the substantive body;
- validate immediately.

## Batch record

For every batch append a record to `BASELINE.md` with:

- date;
- commit or working-tree reference;
- files migrated;
- diagnostics before and after;
- remaining diagnostic groups;
- PHPUnit and PHPStan result.

## Stop conditions

Stop the batch and review when:

- an identifier conflicts with another document;
- the author cannot be established;
- dates conflict across authoritative sources;
- the correct category or class is ambiguous;
- metadata changes would imply a lifecycle decision;
- validation introduces new diagnostic codes.
