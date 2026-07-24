---
id: WP-101-README
title: Engineering Repository Index
summary: Create a deterministic, queryable representation of valid engineering documents discovered through the SIF Builder metadata subsystem.
status: Draft
version: 0.1.0
category: Work Package
document_class: GovernanceDocument
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
  - WP-100
  - EG-003
related_adrs: []
supersedes: null
superseded_by: null
---

# WP-101 — Engineering Repository Index

## Purpose

Create a deterministic, queryable representation of valid engineering documents discovered through the SIF Builder metadata subsystem.

## Incremental delivery

1. Repository index model.
2. Index construction and indexing result.
3. Queries and statistics.
4. Markdown index generation.
5. Reference resolution and dependency graph in a later work package.

## Architectural boundary

The Repository subsystem consumes validated metadata and produces repository-level representations. It SHALL NOT parse Front Matter directly, validate the metadata schema, or resolve document references during the first increment.

## Current increment

Increment 1 stabilizes:

- `RepositoryIndexEntry`;
- `RepositoryIndex`;
- duplicate-identifier behavior;
- deterministic enumeration;
- public model tests.
