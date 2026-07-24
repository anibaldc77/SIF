---
id: EG-015
title: Repository Workflow Integration
summary: Defines integration of discovery, indexing, reference parsing and resolution into the Builder Engine pipeline.
status: Approved
version: 1.0.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-103
authors:
  - SIF Team
created: 2026-07-21
updated: 2026-07-21
tags:
  - builder
  - repository
  - workflow
depends_on: []
related_adrs: []
references: []
---

# EG-015 — Repository Workflow Integration

**Work Package:** WP-103 — Builder Engine  
**Increment:** 4  
**Status:** Implemented  
**Version:** 1.0.0

## 1. Purpose

Integrate the Builder Engine pipeline with the repository capabilities delivered by WP-100, WP-101 and WP-102. The engine must discover governed metadata, construct the repository index, parse references and resolve their targets before analyzers execute.

## 2. Scope

This increment provides:

- a repository workspace carried by `BuilderContext`;
- a discovery stage backed by `RepositoryScannerInterface`;
- an indexing stage backed by `RepositoryIndexBuilder`;
- reference parsing through `ReferenceParserInterface`;
- reference resolution through `ReferenceResolverInterface`;
- stable diagnostics for discovery, parsing and broken references;
- optional injection of repository stages into `BuilderEngine`;
- deterministic repository metrics in context configuration.

Artifact generation, persistence, graph reporting and CLI wiring remain outside this increment.

## 3. Pipeline

```text
PREPARING
  -> DISCOVERING  (metadata registry)
  -> INDEXING     (index + references + resolution)
  -> ANALYZING
  -> GENERATING
  -> FINALIZING
```

The default engine remains backward compatible. When repository stages are not injected, the neutral phase stages introduced by EG-014 are used.

## 4. Repository workspace

`RepositoryWorkspace` is an immutable snapshot holder for:

- `MetadataRegistry`;
- `RepositoryIndex`;
- `ReferenceCollection`;
- `ResolutionResult`.

`BuilderContext::repositoryIndex()` continues to work and returns the index stored in the workspace when available.

## 5. Diagnostics

| Code | Severity | Meaning |
|---|---:|---|
| `REPOSITORY-101` | ERROR | Repository discovery produced an issue |
| `REFERENCE-201` | ERROR | Reference metadata could not be parsed |
| `REFERENCE-404` | ERROR | A referenced target does not exist in the repository index |

Diagnostics contain only safe scalar context and never serialize retained exceptions.

## 6. Determinism

- documents are consumed in `MetadataRegistry::all()` order;
- references are stored using `ReferenceCollection` identity ordering;
- resolution delegates to the deterministic WP-102 resolver;
- repository metrics use stable configuration keys.

## 7. Compatibility

The following public behavior remains valid:

- existing `BuilderContext` construction;
- existing `BuilderEngine` construction;
- existing neutral discovery and indexing stages;
- existing `repositoryIndex()` consumers.

New constructor arguments are optional and appended to existing signatures.

## 8. Acceptance criteria

1. Discovery stores a metadata registry in the context workspace.
2. Discovery issues become `REPOSITORY-101` diagnostics.
3. Indexing requires a completed discovery stage.
4. Indexing creates a repository index.
5. References are parsed from every discovered document.
6. References are resolved against the generated index.
7. Broken targets become `REFERENCE-404` diagnostics.
8. Repository metrics are exposed through context configuration.
9. Existing pipeline behavior remains compatible without injected stages.
10. PHPUnit and PHPStan pass for the complete repository.

## 9. Deferred work

- generated artifact model and writers;
- graph and impact analyzers as registered extensions;
- JSON and Markdown operational reports;
- CLI/bootstrap composition root;
- incremental repository cache.
