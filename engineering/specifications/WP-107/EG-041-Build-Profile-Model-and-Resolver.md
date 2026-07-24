---
id: EG-041
title: Build Profile Model and Resolver
summary: Implemented — WP-107 Increment 2.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - build
  - profile
  - model
  - resolver
work_package: WP-107
depends_on: []
related_adrs: []
supersedes: null
superseded_by: null
---
# EG-041 — Build Profile Model and Resolver

## Status

Implemented — WP-107 Increment 2.

## Objective

Provide an immutable build-profile model and deterministic resolver without coupling the Builder Engine to repository configuration files or CLI concerns.

## Components

- `BuildProfileDefinition`
- `ResolvedBuildProfile`
- `BuildProfileResolutionResult`
- `BuildProfileResolverInterface`
- `BuildProfileResolver`

## Resolution semantics

1. The explicitly selected profile is used when provided; otherwise `default_profile` is used.
2. Inheritance is single and may span multiple levels.
3. A missing child field inherits the resolved parent value.
4. A present child field replaces the parent value completely.
5. An explicit empty list disables all inherited extensions of that category.
6. The default values at the root of an inheritance chain are empty extension lists and `strict=false`.
7. Resolution is deterministic and performs no I/O.

## Validation and diagnostics

- `CONFIG-105`: malformed profile field, unknown field, invalid identifier list, duplicate identifier, or invalid execution option.
- `CONFIG-106`: selected profile does not exist.
- `CONFIG-107`: parent profile does not exist.
- `CONFIG-108`: inheritance cycle.

## Boundaries

This increment does not:

- validate identifiers against the registered extension catalog;
- instantiate analyzers, generators, reporters, or policies;
- integrate profile selection into CLI commands;
- read JSON files directly;
- modify the Builder Engine.

These responsibilities belong to later WP-107 increments.
