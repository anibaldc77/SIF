---
id: EG-003
title: Metadata Subsystem Consolidation
summary: Consolidate metadata capabilities as a stable, reusable SIF Builder subsystem before repository indexing, documentation generation, release automation, and traceability features expand.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-17
updated: 2026-07-17
tags:
  - builder
  - metadata
  - repository
work_package: WP-100
depends_on:
  - ES-002
  - ES-003
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-003 — Metadata Subsystem Consolidation

## Purpose

Consolidate metadata capabilities as a stable, reusable SIF Builder subsystem before repository indexing, documentation generation, release automation, and traceability features expand.

## Architectural boundary

The `Sif\Builder\Metadata` subsystem owns:

- metadata source contracts;
- Markdown Front Matter extraction;
- metadata value representation;
- core metadata validation;
- artifact registration by canonical identifier;
- repository scanning orchestration;
- metadata-specific errors and scan diagnostics.

Consumers may depend on its public contracts and immutable results. The subsystem must not depend on command-line presentation, documentation rendering, release workflows, or a specific repository command.

## Stable structure

```text
tools/builder/src/Metadata/
├── Exception/
├── CoreMetadataValidator.php
├── DocumentClass.php
├── MarkdownFrontMatterReader.php
├── MarkdownRepositoryScanner.php
├── MetadataDocument.php
├── MetadataReaderInterface.php
├── MetadataRegistry.php
├── MetadataScanIssue.php
├── MetadataScanResult.php
├── MetadataValidationError.php
├── MetadataValidationResult.php
├── MetadataValidatorInterface.php
└── RepositoryScannerInterface.php
```

## Dependency direction

```text
Documentation / Release / Commands / Index Generation
                         ↓
              Metadata public contracts
                         ↓
         Metadata parsing, validation and registry
```

Metadata SHALL NOT depend on its consumers.

## Parsing scope

The initial Front Matter reader intentionally supports the ES-002 core subset:

- top-level scalar values;
- quoted scalar values;
- null and boolean values;
- block lists;
- inline lists;
- comments and blank lines.

Nested mappings, anchors, aliases, folded blocks, and general-purpose YAML behavior are outside this increment. A future parser adapter may replace the implementation without changing `MetadataReaderInterface`.

## Registry invariants

- Canonical identifiers are unique within one registry.
- Duplicate identifiers fail explicitly.
- Invalid documents are not registered.
- Registry enumeration is deterministic and sorted by identifier.
- Paths remain available for diagnostics and later traceability.

## Scanner behavior

The repository scanner:

1. traverses the selected root recursively;
2. delegates format support and parsing to `MetadataReaderInterface`;
3. delegates semantic validation to `MetadataValidatorInterface`;
4. registers valid artifacts;
5. accumulates diagnostics instead of stopping at the first invalid document.

## Deferred capabilities

The following remain outside EG-003:

- dependency graph resolution;
- broken-reference detection;
- cycle detection;
- automatic INDEX generation;
- YAML library integration;
- cache and incremental scanning;
- command-line integration.

## Acceptance criteria

- Existing metadata validation remains source compatible.
- New public behaviors are represented by interfaces or immutable value objects.
- Duplicate identifiers are rejected.
- Valid Markdown documents can be discovered recursively.
- Invalid documents produce diagnostics without preventing other valid documents from being indexed.
- PHPUnit and PHPStan level 8 pass.
