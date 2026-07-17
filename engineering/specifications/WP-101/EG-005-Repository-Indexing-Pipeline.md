---
id: EG-005
title: Repository Indexing Pipeline
status: Draft
version: 0.1.0
category: Engineering Specification
document_class: TechnicalSpecification
work_package: WP-101
tags: [builder, repository, indexing, metadata]
---

# EG-005 — Repository Indexing Pipeline

## Purpose

Define the application pipeline that transforms a validated metadata scan into an immutable engineering repository index.

## Scope

This increment introduces orchestration and transformation only. It does not generate Markdown, resolve document references, or calculate grouped repository statistics.

## Components

### RepositoryIndexer

Application entry point. It invokes `RepositoryScannerInterface`, delegates index construction, converts scan issues into repository issues, and records elapsed execution time.

### RepositoryIndexBuilder

Transforms every `MetadataDocument` registered in `MetadataRegistry` into one `RepositoryIndexEntry`. It has no filesystem or Markdown responsibilities.

### RepositoryIndexingResult

Carries the resulting `RepositoryIndex`, non-fatal issues, and elapsed duration. A result is successful only when no issues were reported.

### RepositoryIndexIssue

Immutable diagnostic containing the affected path and a human-readable message.

## Processing flow

```text
RepositoryIndexer
    -> RepositoryScannerInterface::scan()
    -> MetadataScanResult
    -> RepositoryIndexBuilder::build()
    -> RepositoryIndexingResult
```

## Error policy

Invalid or unreadable documents remain scan issues. They do not prevent valid documents from being indexed. Unexpected failures inside the scanner remain the scanner's responsibility.

## Acceptance criteria

- A metadata registry can be transformed into a repository index.
- Scan issues are preserved as repository indexing issues.
- Empty repositories produce an empty successful index.
- Execution duration is non-negative.
- No generated file is written in this increment.
- PHPUnit, PHPStan level 8, Composer validation, and PSR-4 autoloading remain clean.
