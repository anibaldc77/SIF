---
id: EG-306
title: Immutable Migration Value Model
summary: Defines the immutable identifiers, checksums, descriptors, directions, execution modes and requests that form the provider-neutral value boundary of the SIF migration engine.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-07-30
updated: 2026-07-30
work_package: WP-217
tags:
  - foundation
  - database
  - migrations
  - value-model
depends_on:
  - EG-305
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# EG-306 — Immutable Migration Value Model

## 1. Purpose

This increment establishes the immutable public values used by later registry, history, planning and execution increments of WP-217.

## 2. Normative values

The Foundation SHALL provide:

- `MigrationId`, preserving a stable case-sensitive identity;
- `MigrationVersion`, preserving a safe sortable version token without imposing one versioning scheme;
- `MigrationChecksum`, carrying an explicit algorithm and hexadecimal digest;
- `MigrationDirection`, limited to `up` and `down`;
- `MigrationExecutionMode`, limited to `dry-run` and `apply`;
- `MigrationDescriptor`, containing identity, version, checksum, dependencies, reversibility, tags and owner provenance;
- `MigrationRequest`, containing direction, mode, optional target, optional positive limit and optional tag filters.

All values SHALL be immutable and SHALL reject invalid state at construction time through typed migration exceptions.

## 3. Identity and normalization

Migration identities are case-sensitive. Trimming surrounding whitespace is allowed, but internal whitespace, path syntax and executable vocabulary are rejected.

Tags are normalized to lowercase because they are filters and classifications rather than migration identity.

## 4. Checksums

Checksums SHALL retain their algorithm name. The canonical external representation is `algorithm:digest`. SHA-256 is provided as the initial deterministic factory, without preventing future governed algorithms.

The value model does not decide which migration content is canonical; that responsibility belongs to the migration definition boundary and later registry increment.

## 5. Descriptor invariants

A descriptor SHALL reject:

- self-dependencies;
- duplicate dependencies;
- duplicate normalized tags;
- unsafe owner provenance;
- untyped iterable members.

Reversibility is explicit and SHALL NOT be inferred from operation content.

## 6. Request invariants

A request SHALL distinguish read-only dry-run from state-mutating apply mode. Limits, when present, SHALL be positive. Target and tag semantics are interpreted by the planner in a later increment.

## 7. Deferred concerns

This increment intentionally does not provide:

- migration executable contracts;
- registry or graph ordering;
- history records;
- plans or fingerprints;
- database adapters;
- locks or transactions;
- execution authorization.

Those concerns remain governed by EG-305 and subsequent WP-217 increments.
