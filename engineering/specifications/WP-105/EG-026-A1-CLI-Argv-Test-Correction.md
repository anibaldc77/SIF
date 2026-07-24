---
id: EG-026-A1
title: CLI argv test correction
summary: CliApplication::run() receives an ArgvInput whose token list starts with the command name. The executable name (argv[0]) is removed by the CliRunner boundary before the application is called.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-21
updated: 2026-07-22
tags:
  - argv
  - test
  - correction
work_package: WP-105
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-026-A1 — CLI argv test correction

## Status
Accepted

## Context

`CliApplication::run()` receives an `ArgvInput` whose token list starts with the command name. The executable name (`argv[0]`) is removed by the `CliRunner` boundary before the application is called.

The integration test introduced by EG-026 incorrectly passed `bin/sif-builder` as the first command token. The parser therefore treated it as a command name and correctly rejected it because `/` is not valid in a command identifier.

## Decision

The test shall invoke the application with:

```php
new ArgvInput(['list'])
```

Tests targeting `CliRunner` or the executable boundary may include `argv[0]`; tests calling `CliApplication` directly shall not.

## Impact

No production code, public API, parsing rule, or executable behavior is changed.
