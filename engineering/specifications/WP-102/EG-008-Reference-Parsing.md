---
id: EG-008
title: Reference Parsing
summary: Approved for implementation — WP-102 Increment 2.
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
  - parsing
work_package: WP-102
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-008 — Reference Parsing

## Status

Approved for implementation — WP-102 Increment 2.

## Purpose

Transform governed reference declarations in document metadata into the immutable reference model introduced by EG-007. This increment performs syntax parsing and normalization only. It does not resolve targets against the repository index.

## Input

A `MetadataDocument` whose `id` identifies the source document and whose metadata may contain:

- `references`
- `implements`
- `extends`
- `supersedes`
- `related`
- `related_adrs`

Each field accepts `null`, one identifier, or a list of identifiers.

## Identifier convention

Identifiers are trimmed and normalized to uppercase. A valid identifier:

- starts with an ASCII letter;
- contains uppercase letters or digits;
- contains at least one hyphen-separated segment;
- does not contain whitespace or path syntax.

Examples: `ADR-001`, `WP-102`, `SIF-DP-001`, `SPEC-WP-003-RUNTIME-FOUNDATION`.

## Output

`FrontMatterReferenceParser` returns a deterministic `ReferenceCollection`. Every reference records:

- normalized source identifier;
- normalized target identifier;
- relationship type;
- context in the form `front-matter:<field>`.

## Failure policy

Parsing fails explicitly when:

- the source or target identifier is invalid;
- a supported field has an unsupported value type;
- normalization produces a duplicate reference in the same relationship type.

Failures are represented by `ReferenceParseException` and must include the document path and field context.

## Out of scope

- target resolution;
- broken-reference detection;
- circular-dependency analysis;
- repository graph construction;
- implicit references found in prose or arbitrary Markdown links.
