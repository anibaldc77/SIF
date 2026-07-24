---
id: WP-109-IMPLEMENTATION-REPORT
title: WP-109 Repository Discovery Fix Implementation Report
summary: Records the implementation and verification evidence for production repository discovery exclusions.
status: Draft
version: 0.1.0
category: Implementation Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - builder
  - discovery
  - implementation
work_package: WP-109
depends_on:
  - EG-050
related_adrs: []
supersedes: null
superseded_by: null
---

# WP-109 Repository Discovery Fix — Implementation Report

## Changed production component

`tools/builder/src/Metadata/MarkdownRepositoryScanner.php`

The scanner now:

- prunes excluded directories with `RecursiveCallbackFilterIterator`;
- excludes governed generated Markdown output paths;
- never opens or parses excluded files;
- sorts candidates by normalized repository-relative path before parsing;
- supports Windows and POSIX separators;
- applies case-insensitive segment matching.

## Regression tests

`tools/builder/tests/Metadata/MarkdownRepositoryScannerTest.php` covers valid/invalid documents, nested and root dependency trees, generated output paths, substring-safe inclusions, case-insensitive exclusions and deterministic ordering.

## Container verification

- PHP syntax: PASS.
- PHPStan level 8 for the changed production file: PASS.
- `php bin/sif-builder validate`: all seven phases completed.
- Diagnostics in the uploaded repository snapshot: 188.
- Diagnostics containing `vendor`: 0.

The full PHPUnit runner could not start in the container because its PHP installation lacks `dom`, `mbstring` and `xmlwriter`. The definitive suite must run in the supported Windows PHP 8.2.32 environment.
