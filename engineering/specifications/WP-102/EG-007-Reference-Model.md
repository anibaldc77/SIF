---
id: EG-007
title: Reference Model
summary: Define the immutable domain model used to represent typed references between engineering documents.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-20
updated: 2026-07-22
tags:
  - reference
  - model
work_package: WP-102
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-007 — Reference Model

## Status

Approved for implementation.

## Objective

Define the immutable domain model used to represent typed references between engineering documents.

## Scope

This increment includes:

- `Reference`
- `ReferenceType`
- `ReferenceTarget`
- `ReferenceCollection`
- duplicate-reference protection
- deterministic ordering

It explicitly excludes parsing, repository lookup, resolution, graph construction and cycle detection.

## Invariants

1. Source and target identifiers cannot be empty.
2. Line and column positions, when present, are positive integers.
3. A resolved target must exist.
4. A collection cannot contain the same reference identity twice.
5. Collection output is deterministic.
6. The model has no dependency on Metadata or Repository subsystems.

## Reference identity

A reference identity is composed of:

- source identifier
- target identifier
- reference type
- line
- column

Context is descriptive and participates in value equality, but not collection identity.

## Reference types

- `reference`
- `implements`
- `extends`
- `supersedes`
- `related`
