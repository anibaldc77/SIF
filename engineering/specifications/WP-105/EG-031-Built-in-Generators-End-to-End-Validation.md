---
id: EG-031
title: "Built-in Generators End-to-End Validation"
status: Approved
version: 1.0.0
category: "Normative Specification"
document_class: NormativeDocument
authors: [SIF Team]
created: 2026-07-22
updated: 2026-07-22
tags: [builder, generators, testing, e2e]
depends_on: [EG-025, EG-026, EG-027, EG-028, EG-029, EG-030]
related_adrs: []
work_package: WP-105
---

# EG-031 — Built-in Generators End-to-End Validation

## Objective

Close WP-105 by validating the complete production composition of discovery, metadata validation, repository indexing, reference parsing and resolution, generator selection, artifact generation, atomic persistence, reporting, and exit-code mapping.

## Production composition requirement

`DefaultCliApplicationFactory` MUST provide concrete `RepositoryDiscoveryStage` and `RepositoryIndexingStage` instances. Built-in generators MUST receive a populated `RepositoryWorkspace`; phase-only placeholders are insufficient for production execution.

## Acceptance criteria

1. A valid repository builds successfully under strict policy.
2. The default build produces exactly the five governed built-in artifacts.
3. Every artifact is persisted below the approved output root.
4. JSON artifacts are parseable and expose their governed schema.
5. `--generator` limits execution and persistence to the selected generator.
6. No production artifact is written inside the source repository unless explicitly selected as output root.
7. The full PHPUnit and PHPStan suites remain green.

## Governed artifacts

- `engineering/INDEX.generated.md`
- `engineering/REFERENCES.generated.md`
- `engineering/NAVIGATION.generated.md`
- `build/reference-graph.generated.json`
- `build/repository-manifest.generated.json`

## Completion condition

WP-105 is complete when this increment is integrated and all acceptance commands pass in the real repository.
