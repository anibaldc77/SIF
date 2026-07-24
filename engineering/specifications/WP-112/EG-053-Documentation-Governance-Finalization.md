---
id: EG-053
title: Documentation Governance Finalization
summary: Finalizes identifier-to-filename consistency rules and the governed artifact generation workflow.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Team
created: 2026-07-22
updated: 2026-07-22
tags:
  - documentation
  - governance
  - generated-artifacts
  - validation
work_package: WP-112
depends_on:
  - EG-052
  - ES-004
related_adrs: []
supersedes: null
superseded_by: null
---

# EG-053 — Documentation Governance Finalization

## Objective

Finalize documentation governance after the repository metadata migration by removing false-positive filename diagnostics and establishing a repeatable generation workflow for all governed artifacts.

## Scope

- implement ES-004 in `DocumentConsistencyInspector`;
- preserve `DOCCONS-206` for actual governed-identifier mismatches;
- test canonical separators, casing, contextual scope, and real mismatch behavior;
- generate the five built-in governed artifacts through the Builder CLI;
- verify the repository again after generation.

## Governed artifacts

The workflow generates:

- `build/reference-graph.generated.json`;
- `build/repository-manifest.generated.json`;
- `engineering/INDEX.generated.md`;
- `engineering/NAVIGATION.generated.md`;
- `engineering/REFERENCES.generated.md`.

## Acceptance criteria

- the 62 `DOCCONS-206` false positives reported after WP-111 are eliminated;
- formal identifier mismatches remain detectable;
- all five governed artifacts are written atomically by registered generators;
- a subsequent `validate` run emits no `GENART-201` for those artifacts;
- PHPUnit and PHPStan remain successful in the supported PHP 8.2 environment.
