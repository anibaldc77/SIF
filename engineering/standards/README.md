---
id: STANDARDS-README
title: SIF Engineering Standards
summary: This directory contains the cross-project Engineering Standards of the SIF Framework.
status: Draft for Review
version: 1.0.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-16
updated: 2026-07-16
tags:
  - standards
  - engineering
  - governance
work_package: WP-100
depends_on:
  - SIF-CONSTITUTION
  - SAS
related_adrs: []
---

# SIF Engineering Standards

## Purpose

This directory contains the cross-project Engineering Standards of the SIF Framework.

Engineering Standards define mandatory, reusable rules that apply across Work Packages, specifications, architecture records, reviews, policies and Builder-generated artifacts.

## Authority

Engineering Standards operate below the SIF Constitution and the SIF Architecture Specification, and above Work Package specifications.

```text
SIF Constitution
        │
        ▼
SIF Architecture Specification
        │
        ▼
Engineering Standards
        │
        ▼
Policies and ADRs
        │
        ▼
Work Package Specifications
        │
        ▼
Implementation and Verification
```

A lower-level artifact SHALL NOT contradict an applicable Engineering Standard.

## Standard Lifecycle

```text
Draft
  │
  ▼
Technical Review
  │
  ▼
Release Candidate
  │
  ▼
Implementation and Validation
  │
  ▼
Approved
```

Standards MAY later become Deprecated, Superseded or Archived according to the applicable lifecycle and versioning standards.

## Authoring a Standard

1. Reserve an identifier in `INDEX.md`.
2. Copy `TEMPLATE.md`.
3. Complete the YAML Front Matter.
4. Define normative, testable requirements.
5. Document conformance and traceability.
6. Submit the standard for technical review.
7. Update `INDEX.md` when status or version changes.

## Identifier Convention

Engineering Standards use the identifier format:

```text
ES-NNN
```

Examples:

- `ES-001` — Engineering Documentation Standard
- `ES-002` — Metadata Standard
- `ES-003` — Document Class Model

Identifiers are permanent and SHALL NOT be reused.

## Relationship with SIF Builder

SIF Builder is expected to consume approved Engineering Standards through their metadata and derived schemas. Builder implementations SHALL validate artifacts against the Standards and SHALL NOT redefine normative requirements.
