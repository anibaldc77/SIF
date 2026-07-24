---
id: EG-027
title: Reference Report Generator
summary: Generate a deterministic Markdown report that describes the health and topology of repository references using the indexed repository and the result produced by the WP-102 reference resolver.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-21
updated: 2026-07-22
tags:
  - reference
  - report
  - generator
work_package: WP-105
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-027 — Reference Report Generator

## 1. Identification

- Work Package: WP-105
- Increment: 2
- Component: Built-in Generator
- Public identifier: `reference.report`
- Artifact: `engineering/REFERENCES.generated.md`
- Status: Implemented

## 2. Purpose

Generate a deterministic Markdown report that describes the health and topology of repository references using the indexed repository and the result produced by the WP-102 reference resolver.

## 3. Inputs

The generator requires a `RepositoryWorkspace` containing:

1. `RepositoryIndex`;
2. `ResolutionResult`.

Missing required inputs produce diagnostic `GENERATOR-102`. They are expected configuration failures and must not escape as exceptions.

## 4. Output

Exactly one `GeneratedArtifact` is produced:

- generator: `reference.report`;
- path: `engineering/REFERENCES.generated.md`;
- type: `markdown`.

The generator describes artifacts only. Persistence remains the responsibility of `GeneratorStage` and `ArtifactWriterInterface`.

## 5. Report sections

The report contains:

- summary statistics;
- references grouped by semantic type;
- broken references and failure reason;
- ranking of documents by incoming references;
- isolated documents;
- complete resolved-reference table.

A document is isolated when it has no incoming, resolved outgoing, or broken outgoing references.

## 6. Determinism

- repository documents are ordered by natural identifier order;
- resolved and broken references are ordered by source, target, type, and source line;
- reference types are ordered lexicographically;
- ranking uses descending incoming count and identifier as tie-breaker;
- no timestamps, random values, or absolute paths are emitted.

## 7. Public compatibility

The following values are public and stable:

- generator identifier `reference.report`;
- artifact path `engineering/REFERENCES.generated.md`;
- diagnostic code `GENERATOR-102`;
- provenance marker in generated Markdown.

Any incompatible change requires an explicit migration plan.

## 8. CLI integration

`DefaultCliApplicationFactory` registers the generator after `repository.index`. The `list` command must expose both identifiers in registration order.

## 9. Tests

The increment must verify:

- artifact identity and path;
- missing-input diagnostic;
- deterministic statistics and ordering;
- isolated-document detection;
- incoming-reference ranking;
- Markdown escaping;
- visibility through the default CLI composition.
