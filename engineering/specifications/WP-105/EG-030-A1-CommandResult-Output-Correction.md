---
id: EG-030-A1
title: CommandResult Output Correction
summary: The CLI integration test introduced by EG-030 attempted to inspect CommandResult::$output. The CLI core model defined by WP-104 exposes the nullable properties standardOutput and standardError. The undefined property therefore evaluated to.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-21
updated: 2026-07-22
tags:
  - commandresult
  - output
  - correction
work_package: WP-105
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-030-A1 — CommandResult Output Correction

## Status

Accepted

## Context

The CLI integration test introduced by EG-030 attempted to inspect `CommandResult::$output`.
The CLI core model defined by WP-104 exposes the nullable properties `standardOutput` and
`standardError`. The undefined property therefore evaluated to `null`, causing PHPUnit's
`assertStringContainsString()` to fail with a `TypeError` and emit a warning.

## Decision

The test shall inspect `CommandResult::$standardOutput`.

No production source code is changed. The executable behavior and CLI composition were already
correct, as confirmed by `php bin/sif-builder list` exposing `documentation.navigation`.

## Validation

```powershell
vendor\bin\phpunit tools\builder\tests\Cli\Runtime\DefaultCliApplicationFactoryDocumentationNavigationTest.php
vendor\bin\phpunit
vendor\bin\phpstan analyse
php bin\sif-builder list
```
