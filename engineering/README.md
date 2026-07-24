---
id: ENGINEERING-README
title: Engineering System
summary: This directory contains the governed engineering knowledge of SIF. It connects architectural authority, normative rules, implementation planning, reviews and machine-readable validation artifacts.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-17
updated: 2026-07-17
tags:
  - engineering
  - governance
  - navigation
work_package: WP-100
depends_on:
  - ES-001
  - ES-002
related_adrs: []
supersedes: null
superseded_by: null
---

# SIF Engineering System

This directory contains the governed engineering knowledge of SIF. It connects architectural authority, normative rules, implementation planning, reviews and machine-readable validation artifacts.

## Directory Map

| Directory | Purpose |
|---|---|
| `adr/` | Architecture Decision Records that preserve durable decisions and rationale. |
| `models/` | Conceptual models shared by standards, schemas and tooling. |
| `policies/` | Governance policies that regulate engineering processes. |
| `reviews/` | Architecture and implementation review evidence. |
| `schemas/` | Machine-readable contracts derived from normative standards. |
| `specifications/` | Work Packages and detailed architecture or implementation specifications. |
| `standards/` | Normative Engineering Standards. |
| `templates/` | Canonical starting points for governed artifacts. |

## Authority and Traceability

The controlling hierarchy is:

1. SIF Constitution;
2. SIF Architecture Specification;
3. Engineering Standards;
4. Policies;
5. Architecture Decision Records;
6. Work Packages;
7. Specifications;
8. Architecture Reviews;
9. Implementation.

Lower-level artifacts SHALL conform to higher-level authority. Machine-readable schemas and Builder rules implement standards but do not replace them.

## Metadata-First Rule

Every governed Markdown artifact SHALL begin with YAML Front Matter conforming to ES-002 and its applicable document class. `engineering/schemas/metadata.schema.json` is the machine-readable representation of the Core Metadata Schema.

## Contribution Flow

A governed change SHOULD follow:

Need → Architecture → Design → Implementation → Tests → Documentation → Release.

Changes to standards or schemas SHALL preserve traceability between the normative rule, its machine-readable representation and its automated verification.
