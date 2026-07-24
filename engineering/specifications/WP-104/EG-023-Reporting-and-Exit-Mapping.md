---
id: EG-023
title: Reporting and Exit Mapping
summary: Status: Implemented Work Package: WP-104 Increment: 5.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-21
updated: 2026-07-22
tags:
  - reporting
  - exit
  - mapping
work_package: WP-104
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-023 — Reporting and Exit Mapping

Status: Implemented  
Work Package: WP-104  
Increment: 5

## Purpose

Connect terminal execution commands with the deterministic reporters introduced by WP-103 and map Builder outcomes to the stable exit-code contract defined by EG-018.

## Architecture

The implementation introduces three independent responsibilities:

1. `ReporterSelector` selects an Engine-level reporter from a CLI format;
2. `BuilderExitCodeMapper` maps a `BuilderResult` and command type to `ExitCode`;
3. `BuilderCommandResultFactory` applies stdout/stderr policy and creates `CommandResult`.

Commands delegate these responsibilities and do not contain exit mapping or rendering rules.

## Report formats

- default and `markdown`: `report.markdown`;
- `json`: `report.json`.

Unknown report formats are configuration failures.

## Exit mapping

- configuration diagnostics (`CONFIG-*`) map to `CONFIGURATION_ERROR`;
- validate executions with error or fatal diagnostics map to `VALIDATION_FAILED`;
- failed build executions map to `GENERATION_FAILED`;
- build executions with errors and usable artifacts map to `PARTIAL_SUCCESS`;
- build executions with errors and no artifacts map to `GENERATION_FAILED`;
- warning-only and diagnostic-free executions map to `SUCCESS`.

## Stream policy

- rendered reports use standard output;
- safe failure summaries use standard error;
- JSON output is not mixed with progress content;
- quiet mode suppresses successful non-essential output;
- failures remain visible in quiet mode.

## Compatibility

`BuildCommand` and `ValidateCommand` preserve their previous two-argument constructors. The result factory is an optional final dependency.

## Exclusions

This increment does not add executable scripts, process termination, platform wrappers, progress indicators, interactive prompts, or end-to-end process tests. Those belong to Increment 6.
