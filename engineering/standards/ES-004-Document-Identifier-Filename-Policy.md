---
id: ES-004
title: Document Identifier and Filename Policy
summary: Defines canonical filename comparison and contextual identifiers for governed SIF documentation.
status: Draft for Review
version: 0.1.0
category: Engineering Standard
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-22
updated: 2026-07-22
tags:
  - documentation
  - identifiers
  - filenames
  - validation
work_package: WP-112
depends_on:
  - ES-002
  - ES-003
related_adrs: []
supersedes: null
superseded_by: null
---

# ES-004 — Document Identifier and Filename Policy

## 1. Purpose

This standard defines when a governed document identifier is consistent with its filename. It prevents false positives for conventional filenames while retaining strict validation for filenames that carry a formal governed identifier.

## 2. Canonical comparison

For comparison only, identifiers and filename basenames are canonicalized by:

1. trimming surrounding whitespace;
2. converting ASCII letters to uppercase;
3. replacing one or more non-alphanumeric characters with a single hyphen;
4. removing leading and trailing hyphens.

The canonicalization does not rename files or mutate document identifiers.

## 3. Governed filename identifiers

The following filename prefixes are governed identifiers:

- `EG-NNN`;
- `ADR-NNN`;
- `RFC-NNN`;
- `WP-NNN`;
- `ES-NNN`;
- `SIF-DP-NNN`.

When a filename begins with one of these prefixes, its metadata identifier must use the same governed identifier, optionally followed by a more specific suffix.

## 4. Context-scoped identifiers

Conventional or descriptive filenames may use context-scoped identifiers. Examples include:

- `README.md` with `SIF-README`, `FOUNDATION-README`, or `BUILDER-README`;
- `CHANGELOG.md` with a component-specific identifier;
- `01-Foundation.md` with `WP-004-01-FOUNDATION`;
- `repository-discovery.md` with `REPOSITORY-DISCOVERY`;
- migration filenames with stable work-package identifiers.

These filenames do not encode a formal governed identifier and therefore must not trigger `DOCCONS-206` solely because the metadata identifier contains contextual scope.

## 5. Diagnostic rule

`DOCCONS-206` is emitted only when:

- the filename contains a formal governed identifier; and
- the metadata identifier does not match that identifier after canonicalization.

The rule remains a warning because filename correction can require repository-wide reference updates.
