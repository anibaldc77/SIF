---
id: EG-006
title: Repository Documentation Generation
summary: Define the deterministic generation of engineering/INDEX.generated.md from an immutable RepositoryIndex.
status: Draft
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-101
tags:
  - builder
  - repository
  - documentation
  - markdown
authors:
  - SIF Team
created: 2026-07-17
updated: 2026-07-17
depends_on:
  - EG-004
  - EG-005
related_adrs: []
---

# EG-006 — Repository Documentation Generation

## 1. Purpose

Define the deterministic generation of `engineering/INDEX.generated.md` from an immutable `RepositoryIndex`.

## 2. Scope

This increment introduces:

- aggregate repository statistics;
- a writer contract independent of Markdown;
- a deterministic Markdown implementation;
- an application service that coordinates indexing, statistics, and writing.

Reference resolution, dependency graphs, backlinks, and command-line integration remain outside this increment.

## 3. Architecture

```text
GenerateRepositoryIndexService
    ├── RepositoryIndexer
    ├── RepositoryStatistics
    └── RepositoryIndexWriterInterface
            └── MarkdownRepositoryIndexWriter
```

The application layer coordinates the use case. Repository components retain their focused responsibilities.

## 4. Determinism

For an unchanged `RepositoryIndex`, rendering must produce byte-identical output.

Therefore:

- entries are ordered by identifier;
- aggregate keys are ordered lexicographically;
- path separators are normalized to `/`;
- line endings are `LF`;
- no generation timestamp is emitted;
- mutable environment data is excluded.

## 5. Generated artifact

The initial Markdown document contains:

- a generated-file warning;
- total document count;
- counts by category, status, class, and work package;
- a document table with identifier, title, class, category, status, version, work package, and path.

The generated file is derived data and must not become the source of truth.

## 6. File writing

`MarkdownRepositoryIndexWriter` creates the parent directory when necessary and replaces the destination through a temporary file. Failure to create or replace the file is reported through an exception.

## 7. Acceptance criteria

- Empty and populated indexes produce valid Markdown.
- UTF-8 content is preserved.
- Windows paths are normalized.
- repeated rendering is byte-identical;
- statistics are complete and sorted;
- the application service performs the entire generation use case;
- Composer, PHPUnit, PHPStan level 8, and `git diff --check` pass.
