---
id: A1-POWERSHELL-COMPATIBILITY
title: PowerShell 5.1 Compatibility Fix
summary: Replace the use of System.IO.Path.GetRelativePath, unavailable in Windows PowerShell 5.1/.NET Framework, with a System.Uri-based implementation.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - powershell
  - compatibility
work_package: WP-108
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# WP-108-A1 — PowerShell 5.1 Compatibility Fix

## Purpose

Replace the use of `System.IO.Path.GetRelativePath`, unavailable in Windows PowerShell 5.1/.NET Framework, with a `System.Uri`-based implementation.

## Scope

- Keeps the audit script read-only.
- Preserves exclusion and Front Matter classification rules.
- Produces normalized `/` relative paths.
- Requires PowerShell 5.1 or later.
- Does not require PowerShell 7.

## Acceptance

Run from the repository root:

```powershell
powershell -ExecutionPolicy Bypass `
  -File tools\builder\scripts\audit-front-matter.ps1 `
  -RepositoryRoot D:\SIF `
  -CsvPath engineering\migrations\WP-108\front-matter-audit.csv
```

Expected:

- no `GetRelativePath` exception;
- a summary is printed;
- the CSV is generated;
- excluded directories are absent from the inventory.

## A1.1 — Regex normalization correction

The initial compatibility patch used `-replace '\', '/'` with a single backslash pattern in the generated script. In PowerShell, `-replace` interprets its first operand as a regular expression, where a lone backslash is invalid. The normalization now uses the literal string method:

```powershell
[System.Uri]::UnescapeDataString($relativeUri.ToString()).Replace('\', '/')
```

This avoids regular-expression parsing entirely and is compatible with Windows PowerShell 5.1.
