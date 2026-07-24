---
id: EG-050
title: Repository Discovery Fix
summary: Implements the governed Markdown discovery boundary and prunes dependency, generated, cache and transient directories before metadata parsing.
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
  - discovery
  - repository
  - regression
work_package: WP-109
depends_on:
  - EG-048
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-050 — Repository Discovery Fix

## Purpose

Connect the discovery policy defined by EG-048 to the production `MarkdownRepositoryScanner`.

## Problem

The scanner traversed the entire repository with `RecursiveDirectoryIterator` and passed every supported Markdown file to the metadata reader. Consequently, Markdown installed below root and nested `vendor` directories generated `REPOSITORY-101` diagnostics despite not being governed SIF documents.

## Decision

The scanner shall prune excluded directory segments before file parsing, exclude generated Markdown artifact paths, and process candidates in deterministic repository-relative path order.

## Built-in excluded directory segments

`.git`, `.idea`, `.vscode`, `node_modules`, `vendor`, `build`, `dist`, `coverage`, `.cache`, `.phpunit.cache`, `.phpstan.cache`, `.generated`, `generated`, `tmp`, and `temp`.

Matching is case-insensitive and applies to complete directory names. Filenames such as `vendor-policy.md` and `build-profiles.md` remain governed candidates.

## Generated Markdown exclusions

- `engineering/INDEX.generated.md`
- `engineering/REFERENCES.generated.md`
- `engineering/NAVIGATION.generated.md`

The `generated.artifacts` analyzer remains active and unchanged.

## Compatibility

No public interface, CLI command, analyzer, generator, reporter or configuration schema changes. The two-argument `MarkdownRepositoryScanner` constructor remains unchanged.

## Verification contract

Tests shall demonstrate:

1. root and nested dependency directories are excluded;
2. transient and generated directories are excluded;
3. segment matching is case-insensitive;
4. substring matches do not hide governed files;
5. generated Markdown outputs are not rediscovered;
6. governed invalid files still produce issues;
7. candidate processing order is deterministic.

## Acceptance criteria

- No diagnostics originate below a `vendor` directory.
- Existing governed Markdown remains discoverable.
- PHPUnit passes in the supported PHP 8.2.32 environment.
- PHPStan level 8 reports no errors.
- CLI validation completes all seven phases with a diagnostic reduction attributable only to non-governed paths.
