# EG-037 — Generated Artifacts Analyzer

## Status

Implemented — WP-106 Increment 5.

## Identifier

`generated.artifacts`

## Purpose

Provide read-only, deterministic verification of governed generated artifacts without invoking generators or writing files.

## Architectural constraint

Analyzers run before generators. Consequently, missing, empty, stale, conflicting and unregistered generated artifacts are warnings so a strict build can continue into generation and repair them in the same execution. Missing repository inputs remain an analyzer precondition error.

## Inputs

- `BuilderContext::repositoryRoot`
- `BuilderContext::outputRoot` when configured
- `RepositoryWorkspace`
- `MetadataRegistry`
- `GeneratedArtifactCatalog`

## Built-in governed artifacts

- `engineering/INDEX.generated.md`
- `engineering/REFERENCES.generated.md`
- `engineering/NAVIGATION.generated.md`
- `build/reference-graph.generated.json`
- `build/repository-manifest.generated.json`

## Diagnostics

- `ANALYZER-105`: inaccessible repository or unavailable metadata registry.
- `GENART-201`: governed artifact is missing.
- `GENART-202`: governed artifact is empty.
- `GENART-203`: governed artifact is older than the newest indexed metadata source.
- `GENART-204`: generated artifact exists but is not registered in the governed catalog.
- `GENART-205`: governed artifact path is not a regular file.

## Determinism

Catalog entries are sorted by relative path. Findings are sorted by stable identity composed from diagnostic code, source path and message.

## Non-goals

- Re-running generators during analysis.
- Comparing generated content with freshly rendered output.
- Writing, deleting or repairing files.
- Treating timestamps as content hashes.

## Extension model

Additional generated artifacts are introduced by composing a `GeneratedArtifactCatalog` with explicit `GeneratedArtifactDefinition` instances. Duplicate paths are rejected.
