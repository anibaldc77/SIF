---
id: EG-004
title: Engineering Repository Index
summary: Define the stable repository-level model used by SIF Builder to represent valid engineering documents independently from parsing, storage, presentation, and reference resolution.
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
  - repository
  - index
work_package: WP-101
depends_on:
  - EG-003
  - ES-002
  - ES-003
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-004 — Engineering Repository Index

## Purpose

Define the stable repository-level model used by SIF Builder to represent valid engineering documents independently from parsing, storage, presentation, and reference resolution.

## Dependency direction

```text
Metadata subsystem
       ↓
Repository index model
       ↓
Queries / Statistics / Writers / Commands
```

The Repository subsystem MAY depend on public Metadata contracts. Metadata SHALL NOT depend on Repository.

## Increment 1 scope

The first increment provides only the immutable entry model and deterministic collection.

```text
tools/builder/src/Repository/
├── Exception/
│   └── DuplicateRepositoryEntryException.php
├── RepositoryIndex.php
└── RepositoryIndexEntry.php
```

## RepositoryIndexEntry

An entry represents one validated engineering document.

Required properties:

- `identifier`: canonical logical identity;
- `title`: human-readable title;
- `documentClass`: ES-003 document class;
- `category`: metadata category;
- `status`: lifecycle status;
- `version`: document version;
- `path`: repository-relative or absolute physical location.

Optional and repeated properties:

- `workPackage`: owning work package, when applicable;
- `tags`: normalized list of descriptive tags.

### Entry invariants

- Identifier and path SHALL NOT be empty after trimming.
- Scalar values SHALL be immutable after construction.
- Tags SHALL be represented as `list<string>`.
- Empty tags SHALL be discarded.
- Duplicate tags SHALL be removed while preserving their first occurrence.
- The entry SHALL NOT retain Markdown content or perform schema validation.

## RepositoryIndex

The index owns entries keyed by canonical identifier.

Public behavior:

- `add(RepositoryIndexEntry $entry): void`;
- `has(string $identifier): bool`;
- `get(string $identifier): ?RepositoryIndexEntry`;
- `all(): list<RepositoryIndexEntry>`;
- `count(): int`;
- `isEmpty(): bool`.

### Index invariants

- Identifiers SHALL be unique.
- Duplicate insertion SHALL fail explicitly.
- Enumeration SHALL be deterministic and sorted by identifier.
- Returned arrays SHALL not expose mutable internal storage.
- Lookup of an unknown identifier SHALL return `null`.

## Deferred increments

The following are explicitly outside Increment 1:

- scanner orchestration;
- metadata mapping;
- indexing diagnostics;
- category, status, class, work-package, and tag queries;
- aggregate statistics;
- Markdown generation;
- broken-reference and cycle detection.

## Acceptance criteria

- The model compiles under PHP 8.2.
- Public collections carry precise PHPDoc list types.
- Duplicate identifiers report both conflicting paths.
- Entry construction rejects empty identifiers and paths.
- Enumeration remains stable regardless of insertion order.
- PHPUnit and PHPStan level 8 pass.
