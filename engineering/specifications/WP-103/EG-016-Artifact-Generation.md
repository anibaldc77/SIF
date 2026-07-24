---
id: EG-016
title: Artifact Generation
summary: Defines generated artifacts, deterministic collision handling, output-root enforcement and atomic persistence.
status: Approved
version: 1.0.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-103
authors:
  - SIF Team
created: 2026-07-21
updated: 2026-07-21
tags:
  - builder
  - artifacts
  - generation
depends_on: []
related_adrs: []
references: []
---

# EG-016 — Artifact Generation

## Status
Accepted implementation increment for WP-103.

## Purpose
Introduce explicit generated artifacts, deterministic collision detection, approved output-root enforcement, and atomic persistence.

## Decisions
- Generators describe outputs through `GenerationResult`; they do not write files directly.
- `GeneratedArtifact` paths are normalized relative paths and reject absolute paths, empty segments, `.` and `..`.
- `ArtifactCollection` rejects case-insensitive path collisions before persistence.
- `GeneratorStage` aggregates all outputs, validates collisions, and delegates persistence to `ArtifactWriterInterface`.
- `AtomicArtifactWriter` writes a sibling temporary file and publishes it using rename.
- Missing output roots produce `ARTIFACT-101`; write failures produce `ARTIFACT-500`.
- Artifact content is excluded from serialization; SHA-256 is the stable integrity value.

## Compatibility
`GenerationResult` keeps its previous diagnostics-first constructor. Existing generators returning only diagnostics remain valid.

## Deferred
Artifact reporting, execution statistics, manifest generation, CLI output, and CI schemas belong to Increment 6.
